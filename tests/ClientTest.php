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
}
