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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteUserCommand extends Command
{
    /** @var SymfonyStyle $io */
    private $io;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var UserValidatorForCommand $validatorForCommand */
    private $validatorForCommand;

    /** @var UserRepository $userRepository */
    private $userRepository;

    protected static $defaultName = 'app:delete-user';

    /** @var string $defaultDescription */
    protected static $defaultDescription = 'Delete one user in database';

    public function __construct(
        EntityManagerInterface $entityManager,
        UserValidatorForCommand $validatorForCommand,
        UserRepository $userRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->validatorForCommand = $validatorForCommand;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('email', InputArgument::REQUIRED, 'user email')
        ;
    }

    /**
     * Executed after configure().
     * Initialize properties based on the input arguments and options
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io->section("DELETE ONE USER IN DATABASE");
        $this->enterEmail($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new RuntimeException(
                sprintf("NONE USER EXIST IN DATABASE WITH THE FOLLOWING EMAIL %s", $email)
            );
        }

        $userID = $user->getId();
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->io->success("USER WITH ID: {$userID} AND EMAIL: {$email} HAS BEEN DELETED IN DATABASE.");

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
        $emailQuestion->setValidator([$this->validatorForCommand, 'checkEmailForUserDelete']);
        $email = $helper->ask($input, $output, $emailQuestion);
        $input->setArgument('email', $email);
    }
}