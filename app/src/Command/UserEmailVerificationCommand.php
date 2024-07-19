<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\User\EmailVerificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:email-verification',
    description: 'Add a short description for your command',
)]
class UserEmailVerificationCommand extends Command
{
    public function __construct(
        private EmailVerificationService $emailVerificationService,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID of the user send the verification email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getArgument('userId');
        if (!is_numeric($userId)) {
            $io->error('Invalid user ID');

            return Command::FAILURE;
        }

        $user = $this->userRepository->find((int) $userId);
        if (!$user) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        try {
            $this->emailVerificationService->sendNotification(
                user: $user
            );
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('User email verification email successfully sent!');

        return Command::SUCCESS;
    }
}
