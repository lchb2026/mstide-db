<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    public function onLogout(LogoutEvent $event): void
    {
        $event->setResponse(new JsonResponse(['success' => true]));
    }
}
