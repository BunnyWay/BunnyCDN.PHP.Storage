<?php

namespace Bunny\Storage;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider deleteDataProvider
     */
    public function testDelete(string $path, int $statusCode, ?string $expectedExceptionMessage)
    {
        if (null !== $expectedExceptionMessage) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getStatusCode')->willReturn($statusCode);

        $httpClient = $this->createMock(\GuzzleHttp\Client::class);
        $httpClient->expects($this->once())->method('request')->willReturn($response);

        $client = new Client('abc1234d', 'test', Region::FALKENSTEIN, $httpClient);
        $client->delete($path);
    }

    public static function deleteDataProvider(): array
    {
        return [
            ['/a.txt', 200, null],
            ['/a/b/c.txt', 200, null],
            ['/a/b/d', 200, null],
            ['/b.txt', 404, 'Could not find part of the object path: /b.txt'],
            ['/a.txt', 401, 'Authentication failed for storage zone \'test\' with access key \'abc1234d\'.'],
            ['/dir/', 200, null],
        ];
    }

    /**
     * @dataProvider uploadDataProvider
     */
    public function testUpload(string $file, bool $withChecksum, ?string $expectedChecksum)
    {
        $options = function (array $options) use ($expectedChecksum): bool {
            if (!is_resource($options['body'])) {
                return false;
            }

            if (null === $expectedChecksum) {
                return !isset($options['headers']['Checksum']);
            }

            if (!isset($options['headers']['Checksum'])) {
                return false;
            }

            if ($expectedChecksum !== $options['headers']['Checksum']) {
                return false;
            }

            return true;
        };

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $response->expects($this->atLeastOnce())->method('getStatusCode')->willReturn(201);

        $httpClient = $this->createMock(\GuzzleHttp\Client::class);
        $httpClient->expects($this->once())->method('request')->with('PUT', 'test/'.$file, $this->callback($options))->willReturn($response);

        $client = new Client('abc1234d', 'test', Region::FALKENSTEIN, $httpClient);
        $client->upload(__DIR__.'/_files/'.$file, $file, $withChecksum);
    }

    public static function uploadDataProvider(): array
    {
        return [
            ['a.txt', true, 'ECD71870D1963316A97E3AC3408C9835AD8CF0F3C1BC703527C30265534F75AE'],
            ['a.txt', false, null],
            ['bunny-logo.jpg', true, '3A2CBCFCA2CBF58B842B15B08AC69FEC706284CD72CC48058840B4E448AE5949'],
            ['bunny-logo.jpg', false, null],
        ];
    }
}
