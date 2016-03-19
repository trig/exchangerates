<?php

namespace App\Console\Commands;

use App\Services\WebSocketServer\BroadcastApp;
use Illuminate\Console\Command;
use LogicException;
use Ratchet\App as RatchetApp;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionException;
use RuntimeException;

class WebSocketServer extends Command {

    /**
     *
     * @var RatchetApp
     */
    private $application;

    /**
     * Route apps registered in self::$application
     * @var ConnectionInterface
     */
    private $routeApps = [];

    /**
     * Main React loop
     * @var LoopInterface
     */
    private $loop;

    /**
     * Cache key to save pid of main process
     * @var string 
     */
    private $pidCacheKey = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web_socket:server
            {cmd=status : start|stop|restart|status}
            {ip=127.0.0.1}
            {port=5555}
            {--hostname=127.0.0.1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ratchet Web Socket server implementation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->pidCacheKey = self::class . '_pid';

        $this->routeApps = [
            'broadcast' => new BroadcastApp()
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        /* validate command a bit */
        $command = $this->argument('cmd');
        if (!in_array($command, ['start', 'stop', 'restart', 'status']) || !method_exists($this, $command)) {
            throw new LogicException("Command [{$command}] not recognized");
        }

        $this->$command();
    }

    /**
     * Starts server
     */
    private function start() {

        $pid = pcntl_fork();

        if (-1 == $pid) {
            throw new RuntimeException("Can not fork Ratchet server");
        } elseif ($pid) {
            \Cache::put($this->pidCacheKey, $pid, 0);

            $msg = sprintf("[%s] pid of main process is [%s]", self::class, $pid);
            $this->info($msg);
            \Log::info($msg);

            exit(0);
        }

        $ip = $this->argument('ip');
        $port = $this->argument('port');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new LogicException("Ip [{$ip}] is not valid");
        }

        $maxPortValue = pow(2, 16);
        if ($port < 1000 || $port > $maxPortValue) {
            throw new LogicException("Port [{$port}] must be in range 1000 - {$maxPortValue}");
        }

        $this->loop = Factory::create();

        if ($this->loop instanceof StreamSelectLoop) {
            $this->warn(sprintf("Web socket server would perform better if you install libevent php extension."));
            $this->line('');
            $this->line(sprintf("if you running ubuntu  then run:"));
            $this->info(sprintf("sudo su && apt-get install libevent-dev php5-dev php-pear && pecl install channel://pecl.php.net/libevent-0.1.0 && echo \"extension=libevent.so\" > /etc/php5/mods-available/libevent.ini && php5enmod -s cli libevent"));
            $this->line('');
        }

        \Log::debug(sprintf("[%s] using [%s] react loop", self::class, get_class($this->loop)));

        $hostname = $this->option('hostname');

        try {
            $this->application = new RatchetApp($hostname, $port, $ip, $this->loop);
        } catch (ConnectionException $e) {
            $this->error($e->getMessage());
            $this->warn("\ntry to restart or stop first");
            exit($e->getCode());
        }

        foreach ($this->routeApps as $route => $app) {
            $this->application->route($route, $app, ['*']);
        }

        $msg = sprintf('[%s] Starting Ratchet server on [%s:%s] ip and [%s] hostname', self::class, $ip, $port, $hostname);
        $this->info($msg);
        \Log::debug($msg);


        $this->loop->addPeriodicTimer(0.2, function() {
            pcntl_signal_dispatch();
        });

        /* registering pcntl stuff */
        $app = $this;
        foreach ([SIGUSR1, SIGUSR2, SIGTERM] as $signal) {
            pcntl_signal($signal, function($signo)use($app) {
                \Log::info(sprintf('[%s] Handling SIG [%d]', WebSocketServer::class, $signo));
                switch ($signo) {
                    case SIGUSR1:
                        $app->status();
                        break;
                    case SIGUSR2:
                        $app->restart();
                        break;
                    case SIGTERM:
                        $app->stop();
                        break;
                    default:
                        $app->status();
                }
            });
        }

        $this->application->run();
    }

    private function status() {
        /* triggering SIGUSR1 */
        if (null == $this->application) {
            $pid = $this->getPid();
            if (!$pid) {
                $this->info("Ratchet is stopped");
                return;
            }
            posix_kill($pid, SIGUSR1);
            return;
        }

        /* implementation of method (will run in child instance whe $this->application is set) */
        $this->info(sprintf("\nMain server process pid is [%d]", $this->getPid()));
        $this->info("memory usage is " . round(memory_get_usage(true) / (1024 * 1024), 1) . " mB");
        $this->line("\ndumping registered apps:");
        $rows = [];
        foreach ($this->routeApps as $route => $app) {
            $rows[] = [$route, get_class($app), ($app instanceof Countable ? $app->count() : 'n/a')];
        }
        $this->table(['route', 'app class', 'clients online'], $rows);
    }

    private function restart() {
        /* triggering SIGUSR2 */
        if (null == $this->application) {
            posix_kill($this->getPid(), SIGUSR2);
            return;
        }

        $this->info('not implemented. Use [stop] and [start] for now');
    }

    /**
     * Gracefully stops server
     * @return void
     */
    private function stop() {
        /* triggering SIGTERM */
        if (null == $this->application) {
            $pid = $this->getPid();
            if (!$pid) {
                $this->info("Ratchet is stopped");
                return;
            }

            posix_kill($pid, SIGTERM);
            return;
        }

        /* implementation of method (will run in child instance whe $this->application is set) */
        $this->info(sprintf("\nstopping main Ratchet process [%s]", $this->getPid(true)));
        $this->loop->stop();
        unset($this->application);

        $this->info('[ok]');
    }

    /**
     * Returns main pid identifier
     * @param boolean $unregister [optional] if true - then it will unregister pid in storage
     * @return integer 
     */
    private function getPid($unregister = false) {
        if ($unregister) {
            return \Cache::pull($this->pidCacheKey);
        }
        return \Cache::get($this->pidCacheKey, 0);
    }

}
