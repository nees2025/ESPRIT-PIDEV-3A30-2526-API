<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('pages/home.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/destinations', name: 'app_destination')]
    public function destination(): Response
    {
        return $this->render('pages/destination.html.twig');
    }

    #[Route('/hotels', name: 'app_hotel')]
    public function hotel(): Response
    {
        return $this->render('pages/hotel.html.twig');
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('pages/home.html.twig'); // à créer plus tard
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/contact/send', name: 'app_contact_send', methods: ['POST'])]
    public function contactSend(Request $request): Response
    {
        // Traitement du formulaire contact à implémenter
        $this->addFlash('success', 'Votre message a bien été envoyé !');
        return $this->redirectToRoute('app_contact');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Simulation de connexion
            return $this->redirectToRoute('app_home_logged');
        }
        return $this->render('auth/login.html.twig');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Simulation d'inscription
            return $this->redirectToRoute('app_login');
        }
        return $this->render('auth/register.html.twig');
    }

    #[Route('/home-logged', name: 'app_home_logged')]
    public function homeLogged(): Response
    {
        return $this->render('pages/home_logged.html.twig');
    }
}
