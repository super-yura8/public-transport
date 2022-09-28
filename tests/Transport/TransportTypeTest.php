<?php

namespace App\Tests\Transport;

use App\Entity\TransportType;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransportTypeTest extends WebTestCase
{

    use RefreshDatabaseTrait;

    private KernelBrowser $client;
    private EntityManager $em;

    public function setUp(): void
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
        $this->client->request('GET', '/api/transports/types/');
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('id', $data[0]);
    }

    public function testGet(): void
    {
        $type = $this->em->getRepository(TransportType::class)->findOneBy([]);
        $this->client->request('GET', '/api/transports/types/' . $type->getId());
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame($type->getName(), $data['name']);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($type->getId(), $data['id']);
    }

    public function testCreate(): void
    {
        $this->client->request('POST', '/api/transports/types/', content: json_encode([
            'name' => 'test'
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('test', $data['name']);
        $this->assertArrayHasKey('id', $data);
    }

    public function testPatch(): void
    {
        $type = $this->em->getRepository(TransportType::class)->findOneBy(['name' => 'test']);
        $this->client->request('PATCH', '/api/transports/types/' . $type->getId(), content: json_encode([
            'name' => 'test1'
        ]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('test1', $data['name']);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($type->getId(), $data['id']);
    }

    public function testUpdateNotFound(): void
    {
        $this->client->request('PATCH', '/api/transports/types/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateNotValid(): void
    {
        $type = $this->em->getRepository(TransportType::class)->findOneBy([]);
        $this->client->request('PATCH', '/api/transports/types/' . $type->getId(), content: json_encode([
            'name' => ['test']
        ]));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testDelete(): void
    {
        $type = $this->em->getRepository(TransportType::class)->findOneBy(['name' => 'test1']);
        $this->client->request('DELETE', '/api/transports/types/' . $type->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testGetFail(): void
    {
        $this->client->request('GET', '/api/transports/types/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateFail(): void
    {
        $this->client->request('POST', '/api/transports/types/', content: json_encode([

        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testPutFail(): void
    {
        $type = $this->em->getRepository(TransportType::class)->findOneBy([]);
        $this->client->request('PUT', '/api/transports/types/' . $type->getId(), content: json_encode([

        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testDeleteFail(): void
    {
        $this->client->request('DELETE', '/api/transports/types/0');
        $this->assertResponseStatusCodeSame(404);
    }
}