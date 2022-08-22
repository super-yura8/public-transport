<?php

namespace App\Tests\Transport;

use App\Entity\Transport;
use App\Entity\TransportStart;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransportStartTest extends WebTestCase
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
        $this->client->request('GET', '/api/transports/starts/');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertCount(10, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('transport', $data[0]);
        $this->assertArrayHasKey('times', $data[0]);
        $this->assertArrayHasKey('id', $data[0]);
    }

    public function testGet(): void
    {
        $start = $this->em->getRepository(TransportStart::class)->findOneBy([]);
        $this->client->request('GET', '/api/transports/starts/' . $start->getId());
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('times', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($start->getTransport()->getId(),$data['transport']['id']);
        $this->assertSame($start->getTimes(), $data['times']);
        $this->assertSame($start->getId(), $data['id']);
    }

    public function testCreate(): void
    {
        $times = [0,1020];
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $this->client->request('POST', '/api/transports/starts/', content: json_encode([
            'transport' => $transportId,
            'times' => $times
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('times', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($transportId, $data['transport']['id']);
        $this->assertSame($times, $data['times']);
    }


    public function testPatch(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $start = $this->em->getRepository(TransportStart::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('PATCH', '/api/transports/starts/' . $start->getId(), content: json_encode([
            'times' => [232, 300]
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('transport', $data);
        $this->assertArrayHasKey('times', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($start->getTransport()->getId(),$data['transport']['id']);
        $this->assertSame([232, 300], $data['times']);
        $this->assertSame($start->getId(), $data['id']);
    }

    public function testDelete(): void
    {
        $transportId = $this->em->getRepository(Transport::class)->findOneBy(['number' => 1111])->getId();
        $start = $this->em->getRepository(TransportStart::class)->findOneBy(['transport' => $transportId]);
        $this->client->request('GET', '/api/transports/starts/' . $start->getId());
        $this->assertResponseIsSuccessful();

    }

    public function testGetFail(): void
    {
        $this->client->request('GET', '/api/transports/starts/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateFail(): void
    {
        $times = [0,1020];
        $this->client->request('POST', '/api/transports/starts/', content: json_encode([
            'times' => $times
        ]));
        $this->assertResponseStatusCodeSame(409);
    }


    public function testPatchFail(): void
    {
        $start = $this->em->getRepository(TransportStart::class)->findOneBy([]);
        $this->client->request('PATCH', '/api/transports/starts/' . $start->getId(), content: json_encode([
            'times' => 123
        ]));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testDeleteFail(): void
    {
        $this->client->request('GET', '/api/transports/starts/0');
        $this->assertResponseStatusCodeSame(404);

    }
}
