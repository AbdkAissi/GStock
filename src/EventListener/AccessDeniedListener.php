<?php
// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AccessDeniedListener
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Vérifier si l'exception est un AccessDeniedHttpException
        if ($exception instanceof AccessDeniedHttpException) {
            // Logique de redirection ou de gestion d'erreur

            // Rediriger vers la page de connexion ou page personnalisée
            $url = $this->router->generate('app_login'); // Assurez-vous que 'app_login' correspond à la route de votre page de connexion

            // Créer une réponse de redirection
            $response = new Response('', Response::HTTP_FORBIDDEN);
            $response->headers->set('Location', $url);

            // Lancer l'exception pour rediriger
            $event->setResponse($response);
        }
    }
}
