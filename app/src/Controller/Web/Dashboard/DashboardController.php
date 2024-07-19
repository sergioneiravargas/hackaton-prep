<?php

declare(strict_types=1);

namespace App\Controller\Web\Dashboard;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('', name: 'app_web_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Invalid user');
        }

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return $this->redirectToRoute('app_web_dashboard_admin');
        }

        throw $this->createNotFoundException('No dashboard found for this user');
    }
}
