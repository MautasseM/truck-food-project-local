<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        // Création d'un nouvel objet message
        $contactMessage = new ContactMessage();
        
        // Création du formulaire
        $form = $this->createForm(ContactMessageType::class, $contactMessage);

        // Gestion de la requête
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement dans la base de données
            $em->persist($contactMessage);
            $em->flush();

            // Envoi de l'email
            $email = (new Email())
                ->from($this->getParameter('mailer_from')) // Utilisation du paramètre configuré
                ->to('admin@monsite.com') // Destinataire (remplace par la vraie adresse)
                ->subject('Nouveau message de contact')
                ->text(sprintf(
                    "Vous avez reçu un message de %s (%s) :\n\n%s",
                    $contactMessage->getName(),
                    $contactMessage->getEmail(),
                    $contactMessage->getMessage()
                ));

            $mailer->send($email);

            // Message flash de confirmation
            $this->addFlash('success', 'Votre message a bien été envoyé.');

            // Redirection vers la même page après envoi
            return $this->redirectToRoute('app_contact');
        }

        // Affichage de la page avec le formulaire
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
