<?php

namespace App\Services\WebSocketServer;

use Countable;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

/**
 * Server will broadcast message from sender to all connected clients
 */
class BroadcastApp implements MessageComponentInterface, Countable {

    /**
     * Clients db
     * @var \SplObjectStorage
     */
    protected $clients;

    public function __construct() {
        $this->clients = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        \Log::info(sprintf('[%s] registered [%s] client, total clients now [%d]', self::class, $conn->remoteAddress, $this->clients->count()));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        \Log::debug(sprintf('[%s] broadcasting [%s] from [%s] client to [%d] clients', self::class, $msg, $from->remoteAddress, $this->clients->count() - 1));
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        \Log::info(sprintf('[%s] detached [%s] client, total clients now [%d]', self::class, $conn->remoteAddress, $this->clients->count()));
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        \Log::error(sprintf('[%s] error is about: %s', self::class, $e->getMessage()));
        $conn->close();
    }
    
    /**
     * @inheritdoc
     */
    public function count() {
        return $this->clients->count();
    }

}
