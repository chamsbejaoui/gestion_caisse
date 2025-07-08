<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient // ✅ Injecté pour vérifier Turnstile
    ) {}

    public function authenticate(Request $request): Passport
    {
        $payload = $request->getPayload();
        $email = $payload->getString('email');
        $password = $payload->getString('password');
        $csrfToken = $payload->getString('_csrf_token');
        $captchaToken = $payload->getString('cf-turnstile-response'); // ✅ récupéré automatiquement

        // ✅ Vérification CAPTCHA Turnstile via API
        $captchaResponse = $this->httpClient->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret' => '0x4AAAAAABjlKVW6WB7Rfc6OELlstyHh-Ys', // 🔁 Remplace par ta clé secrète
                'response' => $captchaToken,
                'remoteip' => $request->getClientIp(),
            ],
        ]);

        $captchaData = $captchaResponse->toArray();

        if (!$captchaData['success']) {
            throw new CustomUserMessageAuthenticationException('Échec de vérification CAPTCHA.');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function ($userIdentifier) {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    throw new UserNotFoundException('Utilisateur non trouvé.');
                }

                if (!$user->isVerified()) {
                    throw new CustomUserMessageAuthenticationException('Tu dois confirmer ton adresse email avant de te connecter.');
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_user_index'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
