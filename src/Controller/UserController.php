<?php

namespace App\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('api/users')]
class UserController extends AbstractController
{
    #[Route('/current', name: 'app_user')]
    public function index(#[CurrentUser] $user): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $user = $serializer
            ->toArray(
                $user,
                context: SerializationContext::create()->setGroups(['USER_SELF', ...$user->getRoles()])
            );
        return $this->json(
            ['user' => $user],
            Response::HTTP_ACCEPTED,
        );
    }
}
