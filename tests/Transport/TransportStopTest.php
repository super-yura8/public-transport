<?php

namespace App\Tests\Transport;

use App\Entity\TransportStop;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransportStopTest extends WebTestCase
{

    use RefreshDatabaseTrait;

    private KernelBrowser $client;
    private EntityManager $em;

    protected function setUp(): void
    {
        $loader = new NativeLoader();
        $this->client = self::createClient();
        $kernel = static::bootKernel();
        $objectSet = $loader->loadFile($kernel->getProjectDir().'/fixtures/data.yaml');
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client->setServerParameter('HTTP_x-api-token', '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111');

    }

    public function testGetAll(): void
    {
        $this->client->request('GET', '/api/transports/stops/');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertCount(100, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('address', $data[0]);
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/api/transports/stops/', content: json_encode([
            'address' => 'test123'
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('test123', $data['address']);
    }

    public function testGet(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy(['address' => 'test123']);
        $this->client->request('GET', '/api/transports/stops/' . $stop->getId());
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('test123', $data['address']);
    }

    public function testUpdate(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy(['address' => 'test123']);
        $this->client->request('PATCH', '/api/transports/stops/' . $stop->getId(), content: json_encode([
            'address' => 'test1234'
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('test1234', $data['address']);
    }

    public function testDelete(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy(['address' => 'test1234']);
        $this->client->request('GET', '/api/transports/stops/' . $stop->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testCreateFail(): void
    {
        $this->client->request('POST', '/api/transports/stops/', content: json_encode([

        ]));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testGetFail(): void
    {
        $this->client->request('GET', '/api/transports/stops/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateFail(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $this->client->request('PUT', '/api/transports/stops/' . $stop->getId(), content: json_encode([

        ]));
        $this->assertResponseStatusCodeSame(400);

    }

    public function testDeleteFail(): void
    {
        $this->client->request('GET', '/api/transports/stops/0');
        $this->assertResponseStatusCodeSame(404);
    }
}
