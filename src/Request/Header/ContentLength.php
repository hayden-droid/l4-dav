<?php

declare(strict_types=1);

namespace Ngmy\WebDav\Request\Header;

use Ngmy\WebDav\Request;
use RuntimeException;

use function filesize;
use function sprintf;

class ContentLength
{
    private const HEADER_NAME = 'Content-Length';

    /** @var int */
    private $contentLength;

    /**
     * @throws RuntimeException
     */
    public static function createFromFilePath(string $filePath): self
    {
        $contentLength = filesize($filePath);
        if ($contentLength === false) {
            throw new RuntimeException(
                sprintf('Failed to get the size of the file "%s".', $filePath)
            );
        }
        return new self($contentLength);
    }

    public function __construct(int $contentLength)
    {
        $this->contentLength = $contentLength;
    }

    public function __toString(): string
    {
        return (string) $this->contentLength;
    }

    public function provide(Request\Headers $headers): Request\Headers
    {
        return $headers->withHeader(self::HEADER_NAME, (string) $this->contentLength);
    }
}
