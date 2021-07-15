<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Utils\UserValidatorForCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateUserRoleCommand extends Command
{
    protected static $defaultName = 'app:update-user-role';

    /** @var string $defaultDescription */
    protected static $defaultDescription = 'Change the role of one user';

    /** @var SymfonyStyle $io */
    private $io;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var UserValidatorForCommand $validatorCommand */
    private $validatorCommand;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserValidatorForCommand $validatorCommand,
        string $name = null
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->validatorCommand = $validatorCommand;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('email', InputArgument::REQUIRED, 'user email')
            ->addArgument('role', InputArgument::REQUIRED, 'user role')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->section("CHANGE THE ROLE OF ON USER :");
        $this->enterEmail($input, $output);
        $this->enterRole($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new RuntimeException(
                sprintf("NO USER EXIST IN DATABASE WITH THE FOLLOWING EMAIL %s", $email)
            );
        }

        /** @var string $resultRole */
        $resultRole = $input->getArgument('role');
        $role = [$resultRole];
        $user->setRoles($role);
        $this->entityManager->flush();

        $this->io->success("USER ROLE UPDATED WITH SUCCESS.");

        return Command::SUCCESS;
    }

    /**
     * Set input argument email
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterEmail(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $emailQuestion = new Question("USER EMAIL :");
        $emailQuestion->setValidator([$this->validatorCommand, 'validateEmail']);
        $email = $helper->ask($input, $output, $emailQuestion);
        $input->setArgument('email', $email);
    }

    /**
     * Set input argument role
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterRole(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $roleQuestion = new ChoiceQuestion(
            "SELECT USER ROLE",
            [
                'ROLE_USER',
                'ROLE_ADMIN'
            ],
            'ROLE_USER'
        );

        $roleQuestion->setErrorMessage("INVALID USER ROLE");
        $role = $helper->ask($input, $output, $roleQuestion);
        $output->writeln("<info>USER ROLE : {$role}</info>");
        $input->setArgument('role', $role);
    }
}