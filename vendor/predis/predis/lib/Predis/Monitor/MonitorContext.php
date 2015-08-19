<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Monitor;

use Predis\ClientInterface;
use Predis\NotSupportedException;
use Predis\Connection\AggregatedConnectionInterface;

/**
 * Client-side abstraction of a Redis MONITOR context.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MonitorContext implements \Iterator
{
    private $client;
    private $isValid;
    private $position;

    /**
     * @param ClientInterface $client Client instance used by the context.
     */
    public function __construct(ClientInterface $client)
    {
        $this->checkCapabilities($client);
        $this->client = $client;
        $this->openContext();
    }

    /**
     * Automatically closes the context when PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        $this->closeContext();
    }

    /**
     * Checks if the passed client instance satisfies the required conditions
     * needed to initialize a monitor context.
     *
     * @param ClientInterface $client Client instance used by the context.
     */
    private function checkCapabilities(ClientInterface $client)
    {
        if ($client->getConnection() instanceof AggregatedConnectionInterface) {
            throw new NotSupportedException('Cannot initialize a monitor context when using aggregated connections');
        }
        if ($client->getProfile()->supportsCommand('monitor') === false) {
            throw new NotSupportedException('The current profile does not support the MONITOR command');
        }
    }

    /**
     * Initializes the context and sends the MONITOR command to the server.
     */
    protected function openContext()
    {
        $this->isValid = true;
        $monitor = $this->client->createCommand('monitor');
        $this->client->executeCommand($monitor);
    }

    /**
     * Closes the context. Internally this is done by disconnecting from server
     * since there is no way to terminate the stream initialized by MONITOR.
     */
    public function closeContext()
    {
        $this->client->disconnect();
        $this->isValid = false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // NOOP
    }

    /**
     * Returns the last message payload retrieved from the server.
     *
     * @return Object
     */
    public function current()
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Checks if the the context is still in a valid state to continue.
     *
     * @return Boolean
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * Waits for a new message from the server generated by MONITOR and
     * returns it when available.
     *
     * @return Object
     */
    private function getValue()
    {
        $database = 0;
        $client = null;
        $event = $this->client->getConnection()->read();

        $callback = function ($matches) use (&$database, &$client) {
            if (2 === $count = count($matches)) {
                // Redis <= 2.4
                $database = (int) $matches[1];
            }

            if (4 === $count) {
                // Redis >= 2.6
                $database = (int) $matches[2];
                $client = $matches[3];
            }

            return ' ';
        };

        $event = preg_replace_callback('/ \(db (\d+)\) | \[(\d+) (.*?)\] /', $callback, $event, 1);
        @list($timestamp, $command, $arguments) = explode(' ', $event, 3);

        return (object) array(
            'timestamp' => (float) $timestamp,
            'database'  => $database,
            'client'    => $client,
            'command'   => substr($command, 1, -1),
            'arguments' => $arguments,
        );
    }
}
