<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Form\PasswordRecoveryFormType;
use App\Form\RegistrationFormType;
use App\Service\User\EmailVerificationService;
use App\Service\User\Exception\PublicException;
use App\Service\User\PasswordRecoveryService;
use App\Service\User\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_web_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_web_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'title' => 'Login',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_web_logout')]
    #[IsGranted('ROLE_USER')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/registration', name: 'app_web_registration')]
    public function register(
        Request $request,
        RegistrationService $registrationService,
    ): Response {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app_web_dashboard');
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $email = $user->getEmail();
            $plainPassword = $user->getPlainPassword();

            try {
                $registrationService->register($email, $plainPassword);
                $failed = false;
                $message = 'Your user has been successfully registered. Please check your email to verify your email address';
            } catch (\Throwable $e) {
                $failed = true;
                $message = $e instanceof PublicException
                    ? $e->getMessage()
                    : 'An unexpected error occurred while registering your user';
            }

            return $this->render('security/task_feedback.html.twig', [
                'title' => 'Registration',
                'failed' => $failed,
                'message' => $message,
            ]);
        }

        return $this->render('security/task_form.html.twig', [
            'title' => 'Registration',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/email-verification', name: 'app_web_email_verification')]
    #[IsGranted('ROLE_USER')]
    public function verifyEmail(
        Request $request,
        EmailVerificationService $emailVerificationService,
    ): Response {
        $url = $request->getUri();
        try {
            $emailVerificationService->verifyEmail($url);
            $failed = false;
            $message = 'Your email address has been successfully verified';
        } catch (\Throwable $e) {
            $failed = true;
            $message = $e instanceof PublicException
                ? $e->getMessage()
                : 'An unexpected error occurred while verifying your email address';
        }

        return $this->render('security/task_feedback.html.twig', [
            'title' => 'Email verification',
            'failed' => $failed,
            'message' => $message,
        ]);
    }

    #[Route('/password-recovery', name: 'app_web_password_recovery')]
    public function recoverPassword(
        Request $request,
        PasswordRecoveryService $passwordRecoveryService,
    ): Response {
        $url = $request->getUri();

        $form = $this->createForm(PasswordRecoveryFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $plainPassword = $user->getPlainPassword();

            try {
                $passwordRecoveryService->recoverPassword($url, $plainPassword);
                $failed = false;
                $message = 'Your password has been successfully changed';
            } catch (\Throwable $e) {
                $failed = true;
                $message = $e instanceof PublicException
                    ? $e->getMessage()
                    : 'An unexpected error occurred while changing your password';
            }

            return $this->render('security/task_feedback.html.twig', [
                'title' => 'Password recovery',
                'failed' => $failed,
                'message' => $message,
            ]);
        }

        return $this->render('security/task_form.html.twig', [
            'title' => 'Password recovery',
            'form' => $form->createView(),
        ]);
    }
}
