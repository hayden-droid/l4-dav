<?php

declare(strict_types=1);

namespace Ngmy\PhpWebDav;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriInterface;

class CopyParametersBuilder
{
    /**
     * The destination resource URL.
     *
     * @var UriInterface
     */
    private $destinationUrl;
    /**
     * Whether to overwrite the resource if it exists.
     *
     * @var Overwrite
     */
    private $overwrite;

    /**
     * Set the destination resource URL.
     *
     * @param string|UriInterface $destinationUrl The destination resource URL
     * @return $this The value of the calling object
     */
    public function setDestinationUrl($destinationUrl): self
    {
        $this->destinationUrl = Psr17FactoryDiscovery::findUriFactory()->createUri((string) $destinationUrl);
        return $this;
    }

    /**
     * Set whether to overwrite the resource if it exists.
     *
     * @param bool $overwrite Whether to overwrite the resource if it exists
     * @return $this The value of the calling object
     */
    public function setOverwrite(bool $overwrite): self
    {
        $this->overwrite = Overwrite::getInstance($overwrite);
        return $this;
    }

    /**
     * Build a new instance of a parameters class for the WebDAV COPY operation.
     *
     * @return CopyParameters A new instance of a parameter class for the WebDAV COPY operation
     */
    public function build(): CopyParameters
    {
        return new CopyParameters($this->destinationUrl, $this->overwrite);
    }
}
