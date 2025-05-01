<?php
// src/Security/AppAuthenticator.php
namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\SecurityRequestAttributes;


class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    private RouterInterface $router;
    private LoggerInterface $logger;

    public function __construct(RouterInterface $router, LoggerInterface $logger)
    {
        $this->router = $router;
        $this->logger = $logger;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Déboguer les valeurs récupérées
        dump($email, $password); // Ajoute cette ligne pour voir les données envoyées

        if (empty(trim($email))) {
            $this->logger->error('Authentification échouée : email vide', [
                'request_data' => $request->request->all(),
                'ip' => $request->getClientIp()
            ]);
            throw new CustomUserMessageAuthenticationException('L\'email est requis.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        // Vérification plus robuste
        if ($user instanceof User) { // Assurez-vous que User est importé (use App\Entity\User)
            $this->logger->info('Authentication successful for user: ' . $user->getUserIdentifier());
        } else {
            $this->logger->error('User object is not of expected type');
        }

        return new RedirectResponse($this->router->generate('app_dashboard'));
    }
    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            $errorMessage = $exception->getMessage();
        } else {
            $errorMessage = 'Identifiants invalides. Veuillez réessayer.';
        }

        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        return new RedirectResponse($this->getLoginUrl($request));
    }
}
