<?php

namespace App\Services;

/**
 * RouterOS API Client (PHP Socket-based)
 * Compatible with MikroTik RouterOS v6.x & v7.x
 * Port: 8728 (plain) | 8729 (SSL)
 */
class RouterOSAPI
{
    private $socket = null;
    private bool $connected = false;
    private string $host;
    private int $port;
    private int $timeout;

    public function __construct(string $host, int $port = 8728, int $timeout = 10)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->timeout = $timeout;
    }

    /**
     * Connect & login to RouterOS
     */
    public function connect(string $username, string $password): bool
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            throw new \Exception("Cannot connect to {$this->host}:{$this->port} — {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $this->timeout);

        // Login sequence
        $response = $this->communicate(['/login', '=name=' . $username, '=password=' . $password]);

        // RouterOS v6 challenge-response (older firmware)
        if (isset($response[0]) && str_starts_with($response[0], '=ret=')) {
            $challenge = pack('H*', substr($response[0], 5));
            $hash      = md5(chr(0) . $password . $challenge);
            $response  = $this->communicate(['/login', '=name=' . $username, '=response=00' . $hash]);
        }

        if (isset($response[0]) && $response[0] === '!done') {
            $this->connected = true;
            return true;
        }

        throw new \Exception('MikroTik login failed. Check credentials.');
    }

    /**
     * Disconnect cleanly
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket    = null;
            $this->connected = false;
        }
    }

    /**
     * Send command array, get parsed response
     */
    public function query(array $command): array
    {
        if (!$this->connected) {
            throw new \Exception('Not connected to RouterOS.');
        }

        $response = $this->communicate($command);
        return $this->parseResponse($response);
    }

    // ──────────────────────────────────────────────
    // Low-level socket communication
    // ──────────────────────────────────────────────

    private function communicate(array $words): array
    {
        // Write
        foreach ($words as $word) {
            $this->writeWord($word);
        }
        $this->writeWord(''); // end sentence

        // Read
        $response = [];
        while (true) {
            $word = $this->readWord();
            $response[] = $word;
            if ($word === '!done' || str_starts_with($word, '!trap') || str_starts_with($word, '!fatal')) {
                $this->readWord(); // consume trailing empty
                break;
            }
        }

        return $response;
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            fwrite($this->socket, chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            fwrite($this->socket, chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } else {
            fwrite($this->socket, chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        }
        fwrite($this->socket, $word);
    }

    private function readWord(): string
    {
        $len = $this->readLength();
        if ($len === 0) return '';

        $data = '';
        while (strlen($data) < $len) {
            $chunk = fread($this->socket, $len - strlen($data));
            if ($chunk === false || $chunk === '') break;
            $data .= $chunk;
        }
        return $data;
    }

    private function readLength(): int
    {
        $b = ord(fread($this->socket, 1));
        if ($b & 0x80) {
            if ($b & 0x40) {
                if ($b & 0x20) {
                    if ($b & 0x10) {
                        $b  = 0;
                        $b2 = ord(fread($this->socket, 1));
                        $b3 = ord(fread($this->socket, 1));
                        $b4 = ord(fread($this->socket, 1));
                        $b5 = ord(fread($this->socket, 1));
                        return (($b2 << 24) | ($b3 << 16) | ($b4 << 8) | $b5);
                    }
                    $b  &= ~0xE0;
                    $b2  = ord(fread($this->socket, 1));
                    $b3  = ord(fread($this->socket, 1));
                    $b4  = ord(fread($this->socket, 1));
                    return (($b << 24) | ($b2 << 16) | ($b3 << 8) | $b4);
                }
                $b  &= ~0xC0;
                $b2  = ord(fread($this->socket, 1));
                $b3  = ord(fread($this->socket, 1));
                return (($b << 16) | ($b2 << 8) | $b3);
            }
            $b  &= ~0x80;
            $b2  = ord(fread($this->socket, 1));
            return (($b << 8) | $b2);
        }
        return $b;
    }

    private function parseResponse(array $raw): array
    {
        $result  = [];
        $current = [];

        foreach ($raw as $word) {
            if ($word === '!re') {
                if (!empty($current)) $result[] = $current;
                $current = [];
            } elseif ($word === '!done') {
                if (!empty($current)) $result[] = $current;
                break;
            } elseif (str_starts_with($word, '!trap')) {
                throw new \Exception('RouterOS error: ' . $word);
            } elseif (str_starts_with($word, '=')) {
                $parts = explode('=', ltrim($word, '='), 2);
                if (count($parts) === 2) {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }

        return $result;
    }
}
