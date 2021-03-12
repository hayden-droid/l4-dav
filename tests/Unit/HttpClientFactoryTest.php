<?php

declare(strict_types=1);

namespace Ngmy\WebDav\Tests\Unit;

use Http\Client\Curl\Client;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Ngmy\WebDav\HttpClientFactory;
use Ngmy\WebDav\Tests\TestCase;
use Ngmy\WebDav\ClientOptionsBuilder;

class HttpClientFactoryTest extends TestCase
{
    /**
     * @return list<list<mixed>>
     */
    public function createProvider(): array
    {
        return [
            [
                new HttpClientFactory(
                    (new ClientOptionsBuilder())->build()
                ),
                null,
                new Client(
                    MessageFactoryDiscovery::find(),
                    StreamFactoryDiscovery::find(),
                    [
                        \CURLOPT_HTTPAUTH => \CURLAUTH_ANY,
                    ]
                ),
            ],
            [
                new HttpClientFactory(
                    (new ClientOptionsBuilder())
                        ->port(80)
                        ->build()
                ),
                null,
                new Client(
                    MessageFactoryDiscovery::find(),
                    StreamFactoryDiscovery::find(),
                    [
                        \CURLOPT_PORT => 80,
                        \CURLOPT_HTTPAUTH => \CURLAUTH_ANY,
                    ]
                ),
            ],
            [
                new HttpClientFactory(
                    (new ClientOptionsBuilder())
                        ->username('username')
                        ->password('password')
                        ->build()
                ),
                null,
                new Client(
                    MessageFactoryDiscovery::find(),
                    StreamFactoryDiscovery::find(),
                    [
                        \CURLOPT_USERPWD => 'username:password',
                        \CURLOPT_HTTPAUTH => \CURLAUTH_ANY,
                    ]
                ),
            ],
            [
                new HttpClientFactory(
                    (new ClientOptionsBuilder())
                        ->port(80)
                        ->username('username1')
                        ->password('password1')
                        ->build()
                ),
                [
                    \CURLOPT_PROXY => 'http://proxy',
                    \CURLOPT_PORT => 8080,
                    \CURLOPT_USERPWD => 'username2:password2',
                    \CURLOPT_HTTPAUTH => \CURLAUTH_BASIC,
                ],
                new Client(
                    MessageFactoryDiscovery::find(),
                    StreamFactoryDiscovery::find(),
                    [
                        \CURLOPT_PROXY => 'http://proxy',
                        \CURLOPT_PORT => 8080,
                        \CURLOPT_USERPWD => 'username2:password2',
                        \CURLOPT_HTTPAUTH => \CURLAUTH_BASIC,
                    ]
                ),
            ],
        ];
    }

    /**
     * @param array<int, mixed>|null $curlOptions
     * @dataProvider createProvider
     */
    public function testCreate(HttpClientFactory $factory, ?array $curlOptions, HttpClient $expected): void
    {
        $actual = \is_null($curlOptions) ? $factory->create() : $factory->create($curlOptions);
        $this->assertEquals($expected, $actual);
    }
}
