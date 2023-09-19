<?php

namespace Roiwk\SSEClient;

class Client
{
    private string $url;
    private array $streamContextOptions = [];
    private int $retryInterval = 3;
    private array $eventListeners = [];
    private $onmessage = null;
    private $onerror = null;
    private bool $isRunning = false;

    /**
     * Summary of __construct
     * @param string $url
     * @param array $options
     */
    public function __construct(string $url, array $options = [])
    {
        $this->url = $url;
        $this->parseOptions($options);
    }

    /**
     * Summary of parseOptions
     * @param array $options
     * @return void
     */
    private function parseOptions(array $options): void
    {
        if (isset($options['retryInterval'])) {
            $this->retryInterval = $options['retryInterval'];
        }

        if (isset($options['onerror']) && is_callable($options['onerror'])) {
            $this->onerror = $options['onerror'];
        }

        if (isset($options['onmessage']) && is_callable($options['onmessage'])) {
            $this->onmessage = $options['onmessage'];
        }

        if (isset($options['streamContextOptions']) && is_array($options['streamContextOptions'])) {
            $this->streamContextOptions = array_replace_recursive($this->getDefaultStreamContextOptions(), $options['streamContextOptions']);
        } else {
            $this->streamContextOptions = $this->getDefaultStreamContextOptions();
        }
    }

    /**
     * Summary of getDefaultStreamContextOptions
     * @return array
     */
    private function getDefaultStreamContextOptions(): array
    {
        return [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: text/event-stream',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                ],
                // 'content' => '',
            ],
        ];
    }

    private function setStreamContextOptions(array $options): void
    {
        $this->streamContextOptions = array_replace_recursive($this->streamContextOptions, $options);
    }

    public function addEventListener(string $event, $callback): void
    {
        $this->eventListeners[$event][] = $callback;
    }

    public function setRetryInterval(int $retryInterval): void
    {
        $this->retryInterval = $retryInterval;
    }

    public function onerror($callback): void
    {
        $this->onerror = $callback;
    }

    public function onmessage($callback): void
    {
        $this->onmessage = $callback;
    }

    public function close(): void
    {
        $this->isRunning = false;
    }

    /**
     * Summary of start
     * @return void
     */
    public function start(): void
    {
        $this->isRunning = true;

        while (true) {
            try {
                $stream = $this->openStream();
                if (!$stream) {
                    if ($this->onerror) {
                        call_user_func($this->onerror, new \Exception('failed to open stream'));
                    }
                    sleep($this->retryInterval);
                    continue;
                }
                $this->readStream($stream);
                fclose($stream);
            } catch (\Exception $exception) {
                if ($this->onerror) {
                    call_user_func($this->onerror, $exception);
                }
            }
            usleep(100);
        }
    }

    /**
     * Summary of openStream.
     *
     * @return bool|resource
     */
    private function openStream()
    {
        $context = stream_context_create($this->streamContextOptions);
        $stream = fopen($this->url, 'r', false, $context);
        if (!$stream) {
            return false;
        }
        stream_set_timeout($stream, 5);

        return $stream;
    }

    /**
     * Summary of readStream.
     *
     * @param mixed $stream
     */
    private function readStream($stream): void
    {
        $currentEvent = null;
        while (!feof($stream)) {
            if (!$this->isRunning) {
                stream_socket_shutdown($stream, STREAM_SHUT_RDWR);
                exit();
            }

            $line = fgets($stream);
            $line = rtrim($line, "\r\n");

            if (empty($line)) {
                continue;
            }

            if (0 === strpos($line, ':')) {
                continue;
            }

            if (0 === strpos($line, 'event:')) {
                $currentEvent = trim(substr($line, 6));
            }

            if (0 === strpos($line, 'data:')) {
                $data = trim(substr($line, 5));
                if ($this->onmessage && empty($currentEvent)) {
                    call_user_func($this->onmessage, $data);
                } else {
                    $this->dispatchEvent($currentEvent, $data);
                }
            }
        }
    }

    /**
     * Summary of dispatchEvent
     * @param string $event
     * @param string $data
     * @return void
     */
    private function dispatchEvent(string $event, string $data): void
    {
        if (isset($this->eventListeners[$event])) {
            foreach ($this->eventListeners[$event] as $callback) {
                call_user_func($callback, $data);
            }
        }
    }
}
