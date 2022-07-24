<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


class AuthController extends AbstractController
{

    private UserRepository $userRepository;

    private SerializerInterface $serializer;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    #[Route('api/registration', name: 'app_security_auth', methods: ['POST'])]
    public function registration(Request $request): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!is_null($user)) {
            return $this->json([
                'message' => 'User already exist'
            ], Response::HTTP_CONFLICT);
        }

        $user = $this->userRepository->create(['email' => $email, 'password' => $password]);
        return $this->json(
            ['user' => $user],
            Response::HTTP_CREATED,
            context: [AbstractNormalizer::GROUPS => ['user.self']]
        );
    }


    #[Route('/api/login', name: 'api_login')]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (is_null($user)) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $this->userRepository->upgradeApiToken($user, true);
        return $this->json(
            ['user' => $user],
            Response::HTTP_ACCEPTED,
            context: [AbstractNormalizer::GROUPS => ['user.self']]
        );
    }

}
