<?php

namespace App\Tests\User;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class RegistrationAuthenticationTest extends WebTestCase
{

    use RefreshDatabaseTrait;

    private const USER_EMAIL = "test-register-user@uf.com";
    private const USER_PASS = "testPass123";

    private KernelBrowser $client;
    private EntityManager $em;
    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testRegistration(): void
    {
        $this->client->request("POST", "/api/registration", content: json_encode([
                "email" => self::USER_EMAIL,
                "password" => self::USER_PASS
            ]
        ));
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('email', $data['user']);
        $this->assertSame(self::USER_EMAIL, $data['user']['email']);
        $this->assertArrayHasKey('api_token', $data['user']);
    }

    public function testAuthentication()
    {
        $this->client->request("POST", "/api/login", content: json_encode(
            [
                "email" => self::USER_EMAIL,
                "password" => self::USER_PASS
            ]));
        $response = $this->client->getResponse();

        $user = $this->em->getRepository(User::class)->findOneBy(["email" => self::USER_EMAIL]);
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('email', $data['user']);
        $this->assertSame($user->getEmail(), $data['user']['email']);
        $this->assertArrayHasKey('api_token', $data['user']);
    }

    public function testApiTokenAuth()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["email" => self::USER_EMAIL]);
        $this->client->request("GET", "/api/users/current",[], [], [
            "HTTP_x-api-token" => $user->getApiToken(),
        ]);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('email', $data['user']);
        $this->assertSame($user->getEmail(), $data['user']['email']);
        $this->assertArrayHasKey('api_token', $data['user']);
        $this->assertSame($user->getApiToken(), $data['user']['api_token']);

    }

    public function testFailRegistration(): void
    {
        $this->client->request("POST", "/api/registration", content: json_encode(
            [
                "email" => '',
                "password" => self::USER_PASS
            ]
        ));

        $this->assertResponseStatusCodeSame(409);
    }

    public function testFailAuthentication()
    {
        $this->client->request("POST", "/api/login", content: json_encode(
            [
                "email" => self::USER_EMAIL,
                "password" => ''
            ]
        ));
        $this->assertResponseStatusCodeSame(401);

    }

    public function testFailApiTokenAuth()
    {
        $this->client->request("GET", "/api/users/current",[], [], [
            "HTTP_x-api-token" => '',
        ]);
        $this->assertResponseStatusCodeSame(401);

    }

}
