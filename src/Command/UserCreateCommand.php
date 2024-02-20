<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create')]
class UserCreateCommand extends Command
{
    protected ManagerRegistry $doctrine;
    protected UserPasswordHasherInterface $passwordHasher;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create new user.')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Set email.', null)
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Set password.', null)
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Set role.', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email') ?: $io->ask('Enter an email', null, function ($value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Please enter a valid email.');
            }

            $existingUser = $this->doctrine->getManager()->getRepository(User::class)->findOneBy([
                'email' => trim($value),
            ]);

            if ($existingUser) {
                throw new \Exception(sprintf('User %s already exists.', $value));
            }

            return trim($value);
        });

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('Email is invalid.'));

            return 1;
        }

        $password = $input->getOption('password') ?: $io->askHidden('Enter a password', function ($value) {
            if (strlen(trim($value)) < 8) {
                throw new \Exception('Password must have at least 8 characters.');
            }

            return trim($value);
        });

        if(strlen(trim($password)) < 8) {
            $io->error(sprintf('Password must have at least 8 characters.'));

            return 1;
        }

        $roles = [
            'ROLE_EDITOR', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'
        ];

        $role = $input->getOption('role') ?: $io->choice('Select a role', $roles);

        if(!in_array($role, $roles)) {
            $io->error(sprintf('Role "%s" is not a valid role. Available: %s.', $role, implode(', ', $roles)));

            return 1;
        }

        $user = new User();
        $user
            ->setCreatedAt(new \DateTime())
            ->setRoles([$role])
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
        ;

        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

        $io->success(sprintf('User %s created successfully!', $user->getUserIdentifier()));

        return 0;
    }
}
