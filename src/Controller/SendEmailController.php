<?php

namespace App\Controller;

use App\Entity\EmailTransfer;
use App\Form\EmailTransferType;
use App\Repository\EmailTemplateRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/send/email')]
final class SendEmailController extends AbstractController
{
    #[Route('/', name: 'app_send_email')]
    public function new(EntityManagerInterface $entityManager, Request $request, EmailTemplateRepository $emailTemplateRepository, MessageService $messageService): Response
    {
        $emailTransfer = new EmailTransfer();
        $form = $this->createForm(EmailTransferType::class, $emailTransfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $date = new \DateTimeImmutable();
            $emailTransfer->setUser($user);
            $emailTransfer->setEmailTemplate($emailTemplateRepository->find(1));
            $emailTransfer->setCreatedAt($date);
            $emailTransfer->setStatus('Sent');

            $entityManager->persist($emailTransfer);

            $messageService->sendEmail(
                $user->getEmail(),
                $emailTransfer->getRecipientEmail(),
                $emailTransfer->getSubject(),
                $emailTransfer->getMessage(),
            );

            $entityManager->flush();
            $this->addFlash('success', 'Email Sent Successfully!');

            return $this->redirectToRoute('app_send_email');
        }

        return $this->render('send_email/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/history', name: 'app_send_email_history')]
    public function index(): Response
    {
        return $this->render('send_email/index.html.twig', [
        ]);
    }
}
