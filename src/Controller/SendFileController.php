<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/send/file')]
final class SendFileController extends AbstractController
{
    #[Route('/', name: 'app_send_file')]
    public function new(): Response
    {
        return $this->render('send_file/new.html.twig', [
            'controller_name' => 'SendFileController',
        ]);
    }

    #[Route('/history', name: 'app_send_file_history')]
    public function index(): Response
    {
        return $this->render('send_file/index.html.twig', [
            'controller_name' => 'SendFileController',
        ]);
    }
}
