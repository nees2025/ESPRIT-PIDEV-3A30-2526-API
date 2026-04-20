<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\Comment;
use App\Repository\CovoiturageRepository;
use App\Service\AIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
    public function new(Request $request, EntityManagerInterface $entityManager, AIService $aiService, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $covoiturage = new Covoiturage();
            $covoiturage->setDepart($request->request->get('depart'));
            $covoiturage->setDestination($request->request->get('destination'));
            $covoiturage->setDateDepart(new \DateTime($request->request->get('date_depart')));
            $covoiturage->setPlacesDisponibles((int)$request->request->get('places'));
            $type = $request->request->get('type');
            $covoiturage->setType($type);
            
            // INTEG IA: Suggestion intelligente de prix
            $smartPrice = $aiService->suggestSmartPrice($covoiturage->getDepart(), $covoiturage->getDestination(), $type);
            $covoiturage->setPrix($smartPrice);

            $entityManager->persist($covoiturage);
            $entityManager->flush();

            // INTEG MAIL: Envoi de confirmation par email
            $userEmail = $request->request->get('email');
            if ($userEmail) {
                $email = (new Email())
                    ->from('no-reply@descovria.tn')
                    ->to($userEmail)
                    ->subject('Confirmation de votre trajet - Descovria')
                    ->text("Votre trajet de {$covoiturage->getDepart()} vers {$covoiturage->getDestination()} a été publié. Prix suggéré par l'IA: {$smartPrice} DT.");
                
                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Trajet publié et email de confirmation envoyé !');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Trajet publié, mais l\'email n\'a pas pu être envoyé (vérifiez votre config mailer).');
                }
            } else {
                $this->addFlash('success', 'Trajet publié avec succès !');
            }

            return $this->redirectToRoute('app_covoiturage_index');
        }

        return $this->render('covoiturage/new.html.twig');
    }

    #[Route('/{id}', name: 'app_covoiturage_show')]
    public function show(Covoiturage $covoiturage, Request $request, EntityManagerInterface $entityManager, AIService $aiService): Response
    {
        if ($request->isMethod('POST') && $request->request->get('comment')) {
            $comment = new Comment();
            $comment->setAuthor($request->request->get('author', 'Anonyme'));
            $text = $request->request->get('comment');
            $comment->setContent($text);
            $comment->setCovoiturage($covoiturage);

            // INTEG IA: Analyse de sentiment du commentaire
            $sentiment = $aiService->analyzeSentiment($text);
            $comment->setSentiment($sentiment);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('info', "Votre commentaire a été analysé par notre IA comme étant : {$sentiment}");

            return $this->redirectToRoute('app_covoiturage_show', ['id' => $covoiturage->getId()]);
        }

        return $this->render('covoiturage/show.html.twig', [
            'covoiturage' => $covoiturage,
        ]);
    }
}
