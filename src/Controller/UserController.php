<?php

namespace App\Controller;

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
        return $this->json(
            ['user' => $user],
            Response::HTTP_ACCEPTED,
            context: [AbstractNormalizer::GROUPS => ['USER_SELF', ...$user->getRoles()]]
        );
    }
}
