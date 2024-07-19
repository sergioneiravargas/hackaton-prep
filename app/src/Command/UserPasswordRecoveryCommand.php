<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\User\PasswordRecoveryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:password-recovery',
    description: 'Add a short description for your command',
)]
class UserPasswordRecoveryCommand extends Command
{
    public function __construct(
        private PasswordRecoveryService $passwordRecoveryService,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID of the user send the password recovery email');
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
            $this->passwordRecoveryService->sendNotification(
                user: $user
            );
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('User password recovery email successfully sent!');

        return Command::SUCCESS;
    }
}
