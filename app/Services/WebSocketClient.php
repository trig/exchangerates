<?php

namespace App\Services;

use Closure;
use LogicException;
use Ratchet\Client\Connector;
use React\Dns\Protocol\BinaryDumper;
use React\Dns\Protocol\Parser;
use React\Dns\Query\Executor;
use React\EventLoop\Factory as ReactFactory;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use RuntimeException;
use function Ratchet\Client\connect;

class WebSocketClient {

    /**
     * Web socket server url ws://127.0.0.1:5555
     * @var string
     */
    protected $serverUrl = '';

    /**
     * @param string $url [optional] Defaults to ws://127.0.0.1:5555
     * @param string $path [optional] Defaults to /
     * @throws LogicException
     */
    public function __construct($url = 'ws://127.0.0.1:5555', $path = '/') {
        $urlInfo = parse_url($url);

        if (!in_array($urlInfo['scheme'], ['ws', 'wss'])) {
            throw new LogicException("Bad schema in provided url, use ws:// or wss://");
        }

        $this->serverUrl = implode('', [
            $urlInfo['scheme'] . '://',
            $urlInfo['host'],
            ((isset($urlInfo['port']) ? $urlInfo['port'] : 80) != 80 ? ':' . $urlInfo['port'] : ''),
            ('/' == $path ? (isset($urlInfo['path']) ? $urlInfo['path'] : '/') : $path),
            (isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '')
        ]);
    }

    /**
     * Sends data to web socket server
     * 
     * @param string $data Data being sent to server
     * @param Closure $callback [optional] if provided then it'll be called with 
     * response data returned from server
     * @throws RuntimeException if error occured
     */
    public function send($data, Closure $callback = null) {
        $errorHandler = function ($error) {
            \Log::error(sprintf('[%s] %s', WebSocketClient::class, $error));
            throw new RuntimeException($error);
        };

        $client = $this;
        $this->connect($this->serverUrl)->then(function($conn)use($data, $callback, $errorHandler, $client) {
            if ($callback) {
                \Log::debug(sprintf('[%s] registering ws server response callback...', WebSocketClient::class));
                $conn->on('message', function($responseData) use ($conn, $callback) {
                    \Log::debug(sprintf('[%s] forwarding ws server message [%s] to callback...', WebSocketClient::class, $responseData));
                    $callback($responseData);
                    $conn->close();
                });
            }

            $conn->on('error', $errorHandler);


            \Log::debug(sprintf('[%s] Sending data [%s] to [%s] ws server...', WebSocketClient::class, $data, $client->serverUrl));
            $conn->send($data);

            if (null === $callback) {
                \Log::debug(sprintf('[%s] Closing connection due to empty callback', WebSocketClient::class));
                $conn->close();
            }
        }, function($e) use ($errorHandler) {
            $errorHandler($e->getMessage());
        });
    }

    /**
     * We need somehow to deal with non global resolved domains (such from /etc/hosts)
     * so we need inject custom dns resolver
     * 
     * @see connect for original implementation
     * @param string             $url
     * @param array              $subProtocols
     * @param array              $headers
     * @param LoopInterface|null $loop
     * @return PromiseInterface
     */
    function connect($url, array $subProtocols = [], $headers = [], LoopInterface $loop = null) {
        $loop = $loop ? : ReactFactory::create();

        $connector = new Connector($loop, new EtcHostsDnsResolver('', new Executor($loop, new Parser(), new BinaryDumper())));
        $connection = $connector($url, $subProtocols, $headers);

        register_shutdown_function(function() use ($loop) {
            $loop->run();
        });

        return $connection;
    }

}
