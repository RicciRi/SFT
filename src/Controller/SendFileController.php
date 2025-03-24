<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SendFileController extends AbstractController
{
    #[Route('/send/file', name: 'app_send_file')]
    public function index(): Response
    {
        return $this->render('send_file/index.html.twig', [
            'controller_name' => 'SendFileController',
        ]);
    }
}
