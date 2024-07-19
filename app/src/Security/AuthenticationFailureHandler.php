<?php

declare(strict_types=1);

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function __construct(
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        LoggerInterface $logger,
    ) {
        parent::__construct($httpKernel, $httpUtils, FormLoginAuthenticator::CUSTOM_OPTIONS, $logger);
    }
}
