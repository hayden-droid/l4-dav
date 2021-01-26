<?php

declare(strict_types=1);

namespace Ngmy\L4Dav\Tests\Feature;

use GuzzleHttp\Psr7\Uri;
use Ngmy\L4Dav\{
    Credential,
    WebDavClient,
    WebDavClientOptions,
};
use Ngmy\L4Dav\Tests\TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    protected $webdav = '/webdav_no_auth/';

    public function tearDown(): void
    {
        $this->deleteWebDav();

        parent::tearDown();
    }

    public function testPutFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $this->assertEquals('Created', $response->getReasonPhrase());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testDeleteFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');
        $response = $client->delete('file');

        $this->assertEquals('No Content', $response->getReasonPhrase());
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testGetFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->download('file', $path);

        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCopyFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $response = $client->copy('file', 'file2');

        $this->assertEquals('Created', $response->getReasonPhrase());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testMoveFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $response = $client->move('file', 'file2');

        $this->assertEquals('Created', $response->getReasonPhrase());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testMakeDirectory(): void
    {
        $client = $this->createClient();

        $response = $client->makeDirectory('dir/');

        $this->assertEquals('Created', $response->getReasonPhrase());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCheckExistenceDirectoryIfExists(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $response = $client->exists('file');

        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->exists());
    }

    public function testCheckExistenceDirectoryIfNotExists(): void
    {
        $client = $this->createClient();

        $response = $client->exists('file');

        $this->assertEquals('Not Found', $response->getReasonPhrase());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->exists());
    }

    public function testListDirectoryContentsIfDirectoryIsFound(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $client->upload($path, 'file');
        $client->makeDirectory('dir/');
        $client->upload($path, 'dir/file');

        $response = $client->list('');

        $this->assertEquals('Multi-Status', $response->getReasonPhrase());
        $this->assertEquals(207, $response->getStatusCode());
        $this->assertEquals($this->webdav, $response->getList()[0]);
        $this->assertEquals($this->webdav . 'file', $response->getList()[1]);
        $this->assertEquals($this->webdav . 'dir/', $response->getList()[2]);

        $response = $client->list('dir/');

        $this->assertEquals('Multi-Status', $response->getReasonPhrase());
        $this->assertEquals(207, $response->getStatusCode());
        $this->assertEquals($this->webdav . 'dir/', $response->getList()[0]);
        $this->assertEquals($this->webdav . 'dir/file', $response->getList()[1]);
    }

    public function testListDirectoryContentsIfDirectoryIsNotFound(): void
    {
        $client = $this->createClient();

        $response = $client->list('dir/');

        $this->assertEquals('Not Found', $response->getReasonPhrase());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEmpty($response->getList());
    }

    /**
     * @return WebDavClient
     */
    protected function createClient(): WebDavClient
    {
        $options = (new WebDavClientOptions())
            ->setBaseAddress(new Uri('http://apache2' . $this->webdav));
        if (isset($this->username)) {
            $options->setCredential(new Credential($this->username, $this->password));
        }
        return new WebDavClient($options);
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    protected function createTmpFile()
    {
        $file = \tmpfile();
        if ($file === false) {
            throw new RuntimeException();
        }
        return $file;
    }

    protected function deleteWebDav(string $path2 = '')
    {
        $client = $this->createClient();
        foreach ($client->list($path2)->getList() as $path) {
            if ($path == $this->webdav . $path2) {
                continue;
            }
            if (preg_match("|{$this->webdav}(.*\/)$|", $path, $matches)) {
                $this->deleteWebDav($matches[1]);
            }
            $client = $this->createClient();
            $client->delete(preg_replace("|{$this->webdav}(.*)|", '\1', $path));
        }
    }
}
