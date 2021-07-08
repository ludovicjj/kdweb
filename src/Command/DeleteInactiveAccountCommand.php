<?php


namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteInactiveAccountCommand extends Command
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var SymfonyStyle $io */
    private $io;

    protected static $defaultName = 'app:delete-inactive-accounts';

    /** @var string $defaultDescription */
    protected static $defaultDescription = 'Delete inactive user accounts in database';

    public function __construct(
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deleteInactiveAccount();
        return Command::SUCCESS;
    }

    private function deleteInactiveAccount(): void
    {
        $this->io->section("DELETE ALL INACTIVE ACCOUNT IN DATABASE");

        $sql = "DELETE FROM users 
                WHERE account_must_be_verified_before < NOW()
                AND is_verified = false";

        $dbConnection = $this->entityManager->getConnection();
        $statement = $dbConnection->executeQuery($sql);
        $accountDeleted = $statement->rowCount();

        if ($accountDeleted > 1) {
            $result = "{$accountDeleted} INACTIVE USER ACCOUNTS HAVE BEEN DELETED IN DATABASE.";
        } elseif ($accountDeleted === 1) {
            $result = "1 INACTIVE USER ACCOUNT HAVE BEEN DELETED IN DATABASE.";
        } else {
            $result = "NONE INACTIVE USER ACCOUNT HAVE BEEN DELETED IN DATABASE.";
        }

        $this->io->success($result);
    }
}