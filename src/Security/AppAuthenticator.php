<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {

        $login = $request->request->get('login', '');
//        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
//            $email = $login;
//        } else {
//
//        }

        return new Passport(
            new UserBadge($login, function ($userIdentifier) {
                return $this->userRepository->findOneByEmailOrUsername($userIdentifier);
            }),
            new PasswordCredentials($request->request->get('password', ''))
        );

//        $email = $request->getPayload()->getString('email');
//        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);


//        return new Passport(
//            new UserBadge($email),
//            new PasswordCredentials($request->getPayload()->getString('password')),
//            [
//                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
//                new RememberMeBadge(),
//            ]
//        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {


        return new RedirectResponse($this->urlGenerator->generate('app_main'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
