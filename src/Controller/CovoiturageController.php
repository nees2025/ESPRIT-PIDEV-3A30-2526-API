<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\Comment;
use App\Repository\CovoiturageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/covoiturage')]
class CovoiturageController extends AbstractController
{
    #[Route('/', name: 'app_covoiturage_index')]
    public function index(CovoiturageRepository $repository): Response
    {
        return $this->render('covoiturage/index.html.twig', [
            'covoiturages' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_covoiturage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $covoiturage = new Covoiturage();
            $covoiturage->setDepart($request->request->get('depart'));
            $covoiturage->setDestination($request->request->get('destination'));
            $covoiturage->setDateDepart(new \DateTime($request->request->get('date_depart')));
            $covoiturage->setPlacesDisponibles((int)$request->request->get('places'));
            $covoiturage->setType($request->request->get('type'));
            
            // Logique de suggestion de prix
            $basePrice = $covoiturage->getType() === 'bus' ? 10 : 25;
            $suggestedPrice = $basePrice + (rand(5, 15));
            $covoiturage->setPrix((float)$suggestedPrice);

            $entityManager->persist($covoiturage);
            $entityManager->flush();

            return $this->redirectToRoute('app_covoiturage_index');
        }

        return $this->render('covoiturage/new.html.twig');
    }

    #[Route('/{id}', name: 'app_covoiturage_show')]
    public function show(Covoiturage $covoiturage, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST') && $request->request->get('comment')) {
            $comment = new Comment();
            $comment->setAuthor($request->request->get('author', 'Anonyme'));
            $comment->setContent($request->request->get('comment'));
            $comment->setCovoiturage($covoiturage);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_covoiturage_show', ['id' => $covoiturage->getId()]);
        }

        return $this->render('covoiturage/show.html.twig', [
            'covoiturage' => $covoiturage,
        ]);
    }
}
