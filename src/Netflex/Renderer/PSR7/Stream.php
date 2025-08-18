<?php

namespace Netflex\Render\PSR7;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class Stream implements StreamInterface
{
    /** @var string */
    protected $content = '';

    /**
     * Creates a Stream from string
     *
     * @param string $content
     * @return static
     */
    public static function make(string $content): Stream
    {
        $stream = new Stream();
        $stream->content = $content;
        return $stream;
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function close(): void
    {
        throw new RuntimeException('Stream not closable');
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return false;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException('Stream not seekable');
    }

    public function rewind(): void
    {
        throw new RuntimeException('Stream not seekable');
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write($string): int
    {
        $this->content = $string;

        return strlen($string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        return substr($this->content, 0, $length);
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}
