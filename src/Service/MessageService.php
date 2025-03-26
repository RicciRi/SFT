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

    public function sendFileSentNotification($user, $expirationAt, $to, $subject, $message): void
    {
        $html = $this->twig->render('email/send_file_sent_notification.html.twig', [
            'user' => $user,
            'expirationAt' => $expirationAt,
            'subject' => $subject,
            'message' => $message,
        ]);

        $this->sendEmail($to, $subject, $html);
    }

    public function sendEmail($to, $subject, $html): void
    {
        $email = (new Email())
            ->from('sft.mailer@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
