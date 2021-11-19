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
    public static function make(string $content)
    {
        $stream = new static;
        $stream->content = $content;
        return $stream;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function close()
    {
        throw new RuntimeException('Stream not closable');
    }

    public function detach()
    {
        return null;
    }

    public function getSize()
    {
        return strlen($this->content);
    }

    public function tell()
    {
        return 0;
    }

    public function eof()
    {
        return false;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Stream not seekable');
    }

    public function rewind()
    {
        throw new RuntimeException('Stream not seekable');
    }

    public function isWritable()
    {
        return true;
    }

    public function write($string)
    {
        $this->content = $string;
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        return substr($this->content, 0, $length);
    }

    public function getContents()
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}
