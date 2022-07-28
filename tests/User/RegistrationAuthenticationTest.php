<?php

namespace App\Tests\User;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class RegistrationAuthenticationTest extends ApiTestCase
{

    use RefreshDatabaseTrait;

    private const USER_REGISTER_EMAIL = "test-register-user@uf.com";
    private const USER_LOGIN_EMAIL = "test-login-user@uf.com";
    private const USER_PASS = "testPass123";

    private HttpClientInterface $client;
    private EntityManager $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = $this->createClient();
    }

    public function testRegistration(): void
    {
        $this->client->request("POST", "/api/registration", [
            "json" => [
                "email" => self::USER_REGISTER_EMAIL,
                "password" => self::USER_PASS
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            "user" => [
                "email" => self::USER_REGISTER_EMAIL,
            ]
        ]);
    }

    public function testAuthentication()
    {
        $userRepo = $this->em->getRepository(User::class);
        $user = new User();
        $user->setEmail(self::USER_LOGIN_EMAIL);
        $userRepo->hashPassword($user, self::USER_PASS);
        $userRepo->upgradeApiToken($user, true);

        $this->client->request("POST", "/api/login", [
            "json" => [
                "email" => self::USER_LOGIN_EMAIL,
                "password" => self::USER_PASS
            ]
        ]);
        $user = $this->em->getRepository(User::class)->findOneBy(["email" => self::USER_LOGIN_EMAIL]);
        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            "user" => [
                "email" => $user->getEmail(),
            ]
        ]);
    }

    public function testApiTokenAuth()
    {

        $user = $this->em->getRepository(User::class)->findOneBy(["email" => self::USER_LOGIN_EMAIL]);
        $this->client->request("POST", "/api/users/current", [
            "headers" => [
                "x-api-token" => $user->getApiToken(),
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            "user" => [
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "api_token" => $user->getApiToken(),
            ]
        ]);
    }

    public function testFailRegistration(): void
    {
        $this->client->request("POST", "/api/registration", [
            "json" => [
                "email" => '',
                "password" => self::USER_PASS
            ]
        ]);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testFailAuthentication()
    {
        $this->client->request("POST", "/api/login", [
            "json" => [
                "email" => self::USER_LOGIN_EMAIL,
                "password" => ''
            ]
        ]);
        $this->assertResponseStatusCodeSame(401);

    }

    public function testFailApiTokenAuth()
    {
        $this->client->request("GET", "/api/users/current", [
            "headers" => [
                "x-api-token" => '',
            ]
        ]);
        $this->assertResponseStatusCodeSame(401);

    }

}
