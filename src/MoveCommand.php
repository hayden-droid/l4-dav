<?php

declare(strict_types=1);

namespace Ngmy\L4Dav;

use Psr\Http\Message\UriInterface;

class MoveCommand extends Command
{
    /**
     * @param string|UriInterface $srcUri
     * @param string|UriInterface $destUri
     */
    protected function __construct(WebDavClientOptions $options, $srcUri, $destUri)
    {
        $fullDestUri = Url::createFullUrl($destUri, $options->baseUrl());
        parent::__construct($options, 'MOVE', $srcUri, new Headers([
            'Destination' => (string) $fullDestUri,
        ]));
    }
}
