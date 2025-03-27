<?php

namespace App\Service;

use App\Entity\FileTransfer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class MessageService
{
    public function __construct(
        private Environment $twig,
        private MailerInterface $mailer,
        private UriSigner $uriSigner,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
    ) {
    }

    public function sendFileSentNotification($user, $expirationAt, $to, $subject, $message, FileTransfer $fileTransfer): void
    {
        $html = $this->twig->render('email/send_file_sent_notification.html.twig', [
            'user' => $user,
            'expirationAt' => $expirationAt,
            'subject' => $subject,
            'message' => $message,
            'downloadLink' => $this->generateDownloadLink($fileTransfer),
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

    private function generateDownloadLink(FileTransfer $transfer)
    {
        $downloadToken = bin2hex(random_bytes(16));

        $url = $this->router->generate(
            'app_download',
            ['id' => $transfer->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $urlWithToken = $url.'?token='.$downloadToken;

        $signedUrl = $this->uriSigner->sign($urlWithToken);

        $this->logger->info('транфер_ід = '.$transfer->getId(), [
            'url' => $url,
            'urlWithToken = ' => $urlWithToken,
            'signedUrl = ' => $signedUrl,
        ]);

        return $signedUrl;
    }
}
