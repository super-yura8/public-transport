<?php

namespace App\Tests\Transport;

use App\Entity\TransportStop;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
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
        $objectSet = $loader->loadFile($kernel->getProjectDir() . '/fixtures/data.yaml');
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
        $this->client->request('DELETE', '/api/transports/stops/' . $stop->getId());
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateFail(): void
    {
        $this->client->request('POST', '/api/transports/stops/', content: json_encode([

        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetFail(): void
    {
        $this->client->request('GET', '/api/transports/stops/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateFail(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $this->client->request('PUT', '/api/transports/stops/' . $stop->getId());
        $this->assertResponseStatusCodeSame(400);

    }

    public function testUpdateFailInvalid(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $this->client->request('PUT', '/api/transports/stops/' . $stop->getId(), content: json_encode([
            'address' => ['test']
        ]));
        $this->assertResponseStatusCodeSame(400);

    }

    public function testUpdateFailNotFound(): void
    {
        $this->client->request('PUT', '/api/transports/stops/0');
        $this->assertResponseStatusCodeSame(404);

    }

    public function testDeleteFail(): void
    {
        $this->client->request('DELETE', '/api/transports/stops/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetTransports(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $this->client->request('GET', '/api/transports/stops/' . $stop->getId() . '/transports');
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('number', $data[0]);

        $this->assertArrayHasKey('type', $data[0]);
    }

    public function testGetTransportsNotFound(): void
    {
        $this->client->request('GET', '/api/transports/stops/0/transports');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetTransportDestination(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $transportRun = $stop->getTransportRuns()->first();
        $transport = $transportRun->getTransport();
        $arrivalTime = $transportRun->getArrivalTime();
        $times = $transport->getTransportStart()->getTimes();
        $times = array_map(
            function ($x) use ($arrivalTime) {
                return $x + $arrivalTime;
            },
            $times
        );
        $this->client->request('GET', '/api/transports/stops/' . $stop->getId() . '/transports/' . $transport->getId());
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($data, $times);
    }

    public function testGetTransportDestinationRunNotFound(): void
    {
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $transport = $stop->getTransportRuns()->first()->getTransport();
        $this->client->request('GET', '/api/transports/stops/0/transports/' . $transport->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetTransportDestinationStartNotFound(): void
    {
        $this->client->request('GET', '/api/transports/stops/0/transports/0');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetClosestTransportToStopCustomTime(): void
    {
        $minutes = 600;
        $stop = $this->em->getRepository(TransportStop::class)->findOneBy([]);
        $transportRun = $stop->getTransportRuns()->first();
        $transport = $transportRun->getTransport();
        $times = $transport->getTransportStart()->getTimes();
        $arrivalTime = $transportRun->getArrivalTime();
        $beginOfDay = strtotime("today", time()) + $minutes * 60;
        if ($times[count($times) - 1] + $arrivalTime > $minutes) {
            for ($i = 0; $i < count($times); $i++) {
                if ($times[$i] + $arrivalTime >= $minutes) {
                    $closest[] = [
                        'transport' => $transport,
                        'time' => $times[$i] + $arrivalTime
                    ];
                    break;
                }
            }
        }
        $this->client->request('GET', '/api/transports/stops/' . $stop->getId() . '/transports/closest' .
            '?timestamp=' . $beginOfDay);
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(
                new IdenticalPropertyNamingStrategy()
            ))->build();
        $closest = $serializer
            ->toArray(
                $closest,
                context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
            );
        $this->assertEquals($data, $closest);
    }

    public function testGetClosestTransportToStopCustomTimeNotFound(): void
    {
        $this->client->request('GET', '/api/transports/stops/0/transports/closest');
        $this->assertResponseStatusCodeSame(404);
    }
}
