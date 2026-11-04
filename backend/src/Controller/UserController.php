<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'user_login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        if ($this->getUser()) {
            return new JsonResponse([
                'login' => $this->getUser()->getUserIdentifier(),
                'roles' => $this->getUser()->getRoles(),
            ]);
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        return new JsonResponse(['error' => $error?->getMessageKey()], 401);
    }

    #[Route('/logout', name: 'user_logout', methods: ['POST'])]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the firewall.');
    }

    #[Route('/me', name: 'user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['loggedIn' => false]);
        }

        return new JsonResponse([
            'loggedIn' => true,
            'login' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
