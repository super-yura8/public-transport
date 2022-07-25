<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\FormsErrorManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function registration(
        Request $request,
        FormsErrorManager $formsErrorManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $data = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password')
        ];
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            $this->userRepository->upgradeApiToken($user, true);

            return $this->json(
                ['user' => $user],
                Response::HTTP_CREATED,
                context: [AbstractNormalizer::GROUPS => ['USER_SELF']]
            );
        }
        return $this->json([
            'message' => $formsErrorManager->getErrorsFromForm($form)
        ], Response::HTTP_CONFLICT);

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
            context: [AbstractNormalizer::GROUPS => ['USER_SELF', ...$user->getRoles()]]
        );
    }

}
