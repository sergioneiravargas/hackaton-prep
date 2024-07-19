<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\PublicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator as DefaultFormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;

class FormLoginAuthenticator extends DefaultFormLoginAuthenticator
{
    public const CUSTOM_OPTIONS = [
        'login_path' => 'app_web_login',
        'check_path' => 'app_web_login',
        'default_target_path' => 'app_web_dashboard',
        'enable_csrf' => true,
        'use_forward' => false,
    ];

    public function __construct(
        HttpUtils $httpUtils,
        UserProviderInterface $userProvider,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
    ) {
        parent::__construct(
            $httpUtils,
            $userProvider,
            $successHandler,
            $failureHandler,
            self::CUSTOM_OPTIONS,
        );
    }

    public function authenticate(Request $request): Passport
    {
        $passport = parent::authenticate($request);

        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new UnsupportedUserException('Invalid user type');
        }
        if (!$user->isEnabled()) {
            throw new PublicException('User has been disabled', 403);
        }

        return $passport;
    }
}
