<?php

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser()) {
            $this->addFlash('primary', 'Sie sind bereits angemeldet.');
            return $this->redirectToRoute('app_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('guest/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/password', name: 'password')]
    public function password(): Response
    {
        if($this->getUser()) {
            $this->addFlash('primary', 'Sie sind bereits angemeldet.');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('guest/security/password.html.twig');
    }

    #[Route(path: '/registration', name: 'registration')]
    public function registration(): Response
    {
        if($this->getUser()) {
            $this->addFlash('primary', 'Sie sind bereits angemeldet.');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('guest/security/registration.html.twig');
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
