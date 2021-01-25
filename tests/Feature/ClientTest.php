<?php

declare(strict_types=1);

namespace Ngmy\L4Dav\Tests\Feature;

use anlutro\cURL\cURL as Curl;
use Ngmy\L4Dav\{
    Client,
    Credential,
    HttpClient,
    Server,
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

        $this->assertEquals('201 Created', $response->getMessage());
        $this->assertEquals(201, $response->getStatus());
    }

    public function testDeleteFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');
        $response = $client->delete('file');

        $this->assertEquals('204 No Content', $response->getMessage());
        $this->assertEquals(204, $response->getStatus());
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

        $this->assertEquals('200 OK', $response->getMessage());
        $this->assertEquals(200, $response->getStatus());
    }

    public function testCopyFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $response = $client->copy('file', 'file2');

        $this->assertEquals('201 Created', $response->getMessage());
        $this->assertEquals(201, $response->getStatus());
    }

    public function testMoveFile(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $response = $client->move('file', 'file2');

        $this->assertEquals('201 Created', $response->getMessage());
        $this->assertEquals(201, $response->getStatus());
    }

    public function testMakeDirectory(): void
    {
        $client = $this->createClient();

        $response = $client->makeDirectory('dir/');

        $this->assertEquals('201 Created', $response->getMessage());
        $this->assertEquals(201, $response->getStatus());
    }

    public function testCheckExistenceDirectoryIfExists(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $response = $client->upload($path, 'file');

        $result = $client->exists('file');

        $this->assertTrue($result);
    }

    public function testCheckExistenceDirectoryIfNotExists(): void
    {
        $client = $this->createClient();

        $result = $client->exists('file');

        $this->assertFalse($result);
    }

    public function testListDirectoryContentsIfDirectoryIsFound(): void
    {
        $client = $this->createClient();

        $file = $this->createTmpFile();
        $path = stream_get_meta_data($file)['uri'];
        $client->upload($path, 'file');
        $client->makeDirectory('dir/');
        $client->upload($path, 'dir/file');

        $list = $client->list('');

        $this->assertEquals($this->webdav, $list[0]);
        $this->assertEquals($this->webdav . 'file', $list[1]);
        $this->assertEquals($this->webdav . 'dir/', $list[2]);

        $list = $client->list('dir/');

        $this->assertEquals($this->webdav . 'dir/', $list[0]);
        $this->assertEquals($this->webdav . 'dir/file', $list[1]);
    }

    public function testListDirectoryContentsIfDirectoryIsNotFound(): void
    {
        $client = $this->createClient();

        $list = $client->list('dir/');

        $this->assertEmpty($list);
    }

    /**
     * @return Client
     */
    protected function createClient(): Client
    {
        $httpClient = new HttpClient(new Curl());
        $server = Server::of('http://apache2' . $this->webdav);
        if (isset($this->username)) {
            $credential = new Credential($this->username, $this->password);
        }
        return new Client($httpClient, $server, $credential ?? null);
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    protected function createTmpFile()
    {
        $file = tmpfile();
        if ($file === false) {
            throw new RuntimeException();
        }
        return $file;
    }

    protected function deleteWebDav(string $path2 = '')
    {
        $client = $this->createClient();
        foreach ($client->list($path2) as $path) {
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
