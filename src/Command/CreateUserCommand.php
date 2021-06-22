<?php

namespace App\Command;

use App\Entity\User;
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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    /** @var string $defaultDescription */
    protected static $defaultDescription = 'Add a new user in database';

    /** @var SymfonyStyle $io */
    private $io;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var UserValidatorForCommand $validator */
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        UserValidatorForCommand $validator,
        string $name = null
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('email', InputArgument::REQUIRED, 'user email')
            ->addArgument('plainPassword', InputArgument::REQUIRED, 'user plain password')
            ->addArgument('role', InputArgument::REQUIRED, 'user role')
            ->addArgument('isVerified', InputArgument::REQUIRED, 'user status is verified ?')
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

    /**
     * Executed after initialize() and before execute().
     * Check if some options/arguments are missing and ask the user for these values.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->section("ADD USER IN DATABASE");
        $this->enterEmail($input, $output);
        $this->enterPassword($input, $output);
        $this->enterRole($input, $output);
        $this->enterIsVerified($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('plainPassword');

        /** @var string $resultRole */
        $resultRole = $input->getArgument('role');

        $role = [$resultRole];
        $isVerified = $input->getArgument('isVerified') === 'VERIFIED';

        $user = new User();
        $user->setEmail($email)
             ->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword))
             ->setRoles($role)
             ->setIsVerified($isVerified);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success("User created with success");

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
        $emailQuestion->setValidator([$this->validator, 'validateEmail']);

        $email = $helper->ask($input, $output, $emailQuestion);
        if ($this->isAvailableUserEmail($email)) {
            throw new RuntimeException(
                sprintf("ONE USER ALREADY EXIST IN DATABASE WITH THE FOLLOWING EMAIL %s", $email)
            );
        }

        $input->setArgument('email', $email);
    }

    /**
     * Set input argument password
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterPassword(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $passwordQuestion = new Question("USER PLAIN PASSWORD :");
        $passwordQuestion->setValidator([$this->validator, 'validatePassword']);

        // Hide password when typing
        $passwordQuestion
            ->setHidden(true)
            ->setHiddenFallback(false)
        ;

        $password = $helper->ask($input, $output, $passwordQuestion);
        $input->setArgument('plainPassword', $password);
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

    /**
     * Set input argument isVerified
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterIsVerified(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $isVerifiedQuestion = new ChoiceQuestion(
            "SELECT USER ACCOUNT STATUS",
            [
                "VERIFIED",
                "UNVERIFIED",
            ],
            "VERIFIED"
        );
        $isVerifiedQuestion->setErrorMessage("INVALID USER STATUS");
        $status = $helper->ask($input, $output, $isVerifiedQuestion);
        $output->writeln("<info>USER STATUS : {$status}</info>");
        $input->setArgument('isVerified', $status);
    }

    /**
     * Check if an user in database already use the entered email by user from CLI
     *
     * @param string $email
     * @return User|null
     */
    private function isAvailableUserEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
