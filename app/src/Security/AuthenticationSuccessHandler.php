<?php

declare(strict_types=1);

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function __construct(
        HttpUtils $httpUtils,
        LoggerInterface $logger,
    ) {
        parent::__construct($httpUtils, FormLoginAuthenticator::CUSTOM_OPTIONS, $logger);
    }
}
