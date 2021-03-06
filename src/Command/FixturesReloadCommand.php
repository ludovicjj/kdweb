<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesReloadCommand extends Command
{
    protected static $defaultName = 'app:load-fixture';

    /** @var string */
    protected static $defaultDescription = 'Drop database and recreate it with schema and load fixtures';

    /** @var SymfonyStyle $io */
    private $io;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($name);
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

    /**
     * Executed after initialize() and before execute().
     * Check if some options/arguments are missing and ask the user for these values.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->section('Drop database then create Database with schema and load fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->runSymfonyCommand('doctrine:database:drop', ["--force" => true]);
        $this->runSymfonyCommand('doctrine:database:create', ["--if-not-exists" => true]);
        $this->runSymfonyCommand('doctrine:migrations:migrate', ["--no-interaction" => true]);
        $this->runSymfonyCommand('doctrine:fixtures:load', ["--no-interaction" => true]);
        $this->createRememberMeTokenTable();

        $this->io->success('Recreate database with schema and load fixtures with success');

        return Command::SUCCESS;
    }

    /**
     * @param string $command
     * @param array<string, boolean> $options
     */
    private function runSymfonyCommand(
        string $command,
        array $options = []
    ): void {
        $application = $this->getApplication();

        if (!$application) {
            throw new LogicException("no application...");
        }

        $application->setAutoExit(false);

        $options["command"] = $command;

        try {
            $application->run(new ArrayInput($options));
        } catch (Exception $exception) {
            throw new LogicException(sprintf("Command %s fail", $command));
        }
    }

    private function createRememberMeTokenTable(): void
    {
        $sql = "CREATE TABLE `rememberme_token` (
            `series`   char(88)     UNIQUE PRIMARY KEY NOT NULL,
            `value`    varchar(88)  NOT NULL,
            `lastUsed` datetime     NOT NULL,
            `class`    varchar(100) NOT NULL,
            `username` varchar(200) NOT NULL
        );";

        try {
            $this->entityManager->getConnection()->executeStatement($sql);
        } catch (\Doctrine\DBAL\Exception $e) {
        }
    }
}
