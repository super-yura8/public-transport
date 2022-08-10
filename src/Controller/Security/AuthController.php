<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\FormsErrorManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api', name: 'api_')]
class AuthController extends AbstractController
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function registration(
        Request $request,
        FormsErrorManager $formsErrorManager,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->userRepository->hashPassword($user, $user->getPassword());
            $this->userRepository->upgradeApiToken($user, true);
            $serializer = SerializerBuilder::create()->build();

            $user = $serializer
                ->toArray(
                    $user,
                    context: SerializationContext::create()->setGroups(['USER_SELF', ...$user->getRoles()])
                );
            return $this->json(['user' => $user], Response::HTTP_CREATED);
        }
        return $this->json([
            'message' => $formsErrorManager->getErrorsFromForm($form)
        ], Response::HTTP_CONFLICT);

    }


    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (is_null($user)) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $this->userRepository->upgradeApiToken($user, true);
        $serializer = SerializerBuilder::create()->build();
        $user = $serializer
            ->toArray(
                $user,
                context: SerializationContext::create()->setGroups(['USER_SELF', ...$user->getRoles()])
            );
        return $this->json(['user' => $user], Response::HTTP_ACCEPTED,
        );
    }

}
