<?php
declare(strict_types=1);

namespace app;

use app\exceptions\ClientException;
use app\exceptions\CommunicationException;

class Client
{
    private string $uri;

    /**
     * @var resource
     */
    private $streamContext;
    public bool $isOpen = false;
    private bool $persistent;
    /**
     * @var resource
     */
    private $stream;

    public function __construct(string $host, string $port, bool $tcpNoDelay = true, bool $persistent = false)
    {
        $this->uri = "tcp://${host}:{$port}";

        $this->streamContext = \stream_context_create([
            'socket' =>
                [
                    'tcp_nodelay' => $tcpNoDelay
                ]
        ]);
        $this->persistent = $persistent;
    }

    public function open(): void
    {
        if ($this->stream) {
            return;
        }

        $flags = $this->persistent
            ? \STREAM_CLIENT_CONNECT | \STREAM_CLIENT_PERSISTENT
            : \STREAM_CLIENT_CONNECT;

        $stream = @\stream_socket_client(
            $this->uri,
            $errorCode,
            $errorMessage,
            5.0,
            $flags,
            $this->streamContext
        );

        if (false === $stream) {
            throw new ClientException($errorMessage);
        }
        \stream_set_timeout($stream, 5);

        $this->stream = $stream;
        $meta = stream_get_meta_data($stream);
        if ($this->persistent && \ftell($stream)) {
            return;
        }


        $s = $this->read();
        if (!str_starts_with($s, 'Greeting')) {
            throw new ClientException('Bad greetings: ' . $s);
        }
    }

    public function close() : void
    {
        if ($this->stream) {
            \fclose($this->stream);
        }

        $this->stream = null;
        $this->isOpen = false;
    }

    public function send(string $data) : string
    {
        if (!$this->stream || !\fwrite($this->stream, $data)) {
            throw new CommunicationException('Unable to write request');
        }

        return '';
    }

    public function read() : string
    {
        $lenBytes = \stream_get_contents($this->stream, 4);
        if (! $lenBytes) {
            $meta = \stream_get_meta_data($this->stream);
            throw new CommunicationException($meta['timed_out'] ? 'Read timed out' : 'Error on read');
        }

        ['len' => $packetLength] = unpack('Nlen', $lenBytes);
        if ($data = \stream_get_contents($this->stream, $packetLength)) {
            return $data;
        }

        $meta = \stream_get_meta_data($this->stream);
        throw new CommunicationException($meta['timed_out'] ? 'Read timed out' : 'Error on send');
    }
}