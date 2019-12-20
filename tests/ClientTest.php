<?php

use OsmClient\OsmClient;
use OsmClient\OsmClientException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testException()
    {
        $message = 'Just a test exception';
        $ex = new OsmClientException($message);

        $this->assertEquals(500, $ex->getHttpCode());
        $this->assertEquals($message, $ex->getMessage());

        $ex = new OsmClientException($message, 400);
        $this->assertEquals(400, $ex->getHttpCode());
    }

    /**
     * @throws OsmClientException
     */
    public function testClient()
    {
        $client = new OsmClient('UA');
        $place = $client->findOne('Kyiv');

        $this->assertArrayHasKey('address', $place);
        $this->assertArrayHasKey('lat', $place);
        $this->assertArrayHasKey('lon', $place);

        echo sprintf(
            "\nFound: %s, %s (%s,%s)",
            $place['address']['country'],
            $place['address']['city'],
            $place['lat'],
            $place['lon']
        );
    }
}
