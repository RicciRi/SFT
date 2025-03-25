<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MessageService
{
    public function __construct(
        private Environment $twig,
        private MailerInterface $mailer,
    ) {
    }

    public function sendEmail($from, $to, $subject, $message)
    {
        $html = $this->twig->render('email/send_file.html.twig', [
            'from' => $from,
            'message' => $message,
        ]);

        $email = (new Email())
            ->from('sft.mailer@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
