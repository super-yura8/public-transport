<?php

namespace App\Tests\Transport;

use App\Entity\Transport;
use App\Entity\TransportRun;
use App\Entity\TransportStop;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransportRunsTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private KernelBrowser $client;
    private EntityManager $em;

    protected function setUp(): void
    {
        $loader = new NativeLoader();
        $this->client = self::createClient();
        $kernel = static::bootKernel();
        $objectSet = $loader->loadFile($kernel->getProjectDir() . '/fixtures/data.yaml');
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client->setServerParameter('HTTP_x-api-token', '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111');

    }

    public function testGetAll(): void
    {
        $this->client->request('GET', '/api/transports/runs/');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertCount(100, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('transport', $data[0]);
        $this->assertArrayHasKey('transportStop', $data[0]);
        $this->assertArrayHasKey('id', $data[0]);
    }

    public function testGet(): void
    {
        $run = $this->em->getRepository(TransportRun::class)->findOneBy([]);
        $this->client->request('GET', '/api/transports/runs/' . $run->getId());
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('transportStop', $data);
        $this->assertArrayHasKey('arrivalTime', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($run->getTransport()->getId(), $data['transport']['id']);
        $this->assertSame($run->getTransportStop()->getId(), $data['transportStop']['id']);
        $this->assertSame($run->getArrivalTime(), $data['arrivalTime']);
        $this->assertSame($run->getId(), $data['id']);
    }

    public function testCreate(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $transportStopId = $this->em->getRepository(TransportStop::class)->findOneBy([])->getId();
        $this->client->request('POST', '/api/transports/runs/', content: json_encode([
            'transport' => $transportId,
            'transportStop' => $transportStopId,
            'arrivalTime' => 1111
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('transportStop', $data);
        $this->assertArrayHasKey('arrivalTime', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($transportId, $data['transport']['id']);
        $this->assertSame($transportStopId, $data['transportStop']['id']);
        $this->assertSame(1111, $data['arrivalTime']);
    }


    public function testPatch(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $run = $this->em->getRepository(TransportRun::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('PATCH', '/api/transports/runs/' . $run->getId(), content: json_encode([
            'arrivalTime' => 333
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('transportStop', $data);
        $this->assertArrayHasKey('arrivalTime', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($run->getTransport()->getId(), $data['transport']['id']);
        $this->assertSame($run->getTransportStop()->getId(), $data['transportStop']['id']);
        $this->assertSame(333, $data['arrivalTime']);
        $this->assertSame($run->getId(), $data['id']);
    }

    public function testDelete(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $run = $this->em->getRepository(TransportRun::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('DELETE', '/api/transports/runs/' . $run->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteNotFound(): void
    {
        $this->client->request('DELETE', '/api/transports/runs/0');
        $this->assertResponseStatusCodeSame(404);
    }


    public function testGetFail(): void
    {
        $this->client->request('GET', '/api/transports/runs/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateFail(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy([])->getId();
        $this->client->request('POST', '/api/transports/runs/', content: json_encode([
            'transport' => $transportId,
            'arrivalTime' => 1111
        ]));
        $this->assertResponseStatusCodeSame(400);
    }


    public function testPatchFail(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy([])->getId();
        $run = $this->em->getRepository(TransportRun::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('PATCH', '/api/transports/runs/' . $run->getId(), content: json_encode([
            'arrivalTime' => 'test'
        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testPutFail400(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy([])->getId();
        $run = $this->em->getRepository(TransportRun::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('PUT', '/api/transports/runs/' . $run->getId(), content: json_encode([
            'arrivalTime' => 'test'
        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateNotFound(): void
    {
        $this->client->request('PUT', '/api/transports/runs/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteFail(): void
    {
        $this->client->request('GET', '/api/transports/runs/0');
        $this->assertResponseStatusCodeSame(404);
    }
}
