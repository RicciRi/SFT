<?php

namespace App\EventListener;

use App\Enum\FlashTypes;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    public function __construct(
        private RouterInterface $router,
        private RequestStack $requestStack,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            $session = $this->requestStack->getSession();
            if ($session) {
                $session->getFlashBag()->add(FlashTypes::WARNING->value, 'Access denied. Please log in with appropriate credentials.');
            }

            $url = $this->router->generate('app_home');

            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}
