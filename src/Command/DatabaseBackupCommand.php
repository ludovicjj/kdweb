<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupCommand extends Command
{
    protected static $defaultName = 'app:database-backup';
    protected static $defaultDescription = 'Create database back-up';

    /** @var string $projectDir */
    private $projectDir;

    /** @var ManagerRegistry $managerRegistry */
    private $managerRegistry;

    /** @var SymfonyStyle $io */
    private $io;

    public function __construct(
        string $projectDir,
        ManagerRegistry $managerRegistry,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->projectDir = $projectDir;
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $backupDir = "{$this->projectDir}/var/backup";

        if ($fileSystem->exists($backupDir)) {
            $fileSystem->remove($backupDir);
        }

        try {
            $fileSystem->mkdir($backupDir, 0700);
        } catch (IOException $error) {
            throw new IOException($error);
        }

        /** @var Connection $connection */
        $connection = $this->managerRegistry->getConnection();

        [
            "host" => $databaseHost,
            "port" => $databasePort,
            "user" => $databaseUsername,
            "password" => $databasePassword,
            "dbname" => $databaseName
        ] = $connection->getParams();

        $fileTargetPath = "--result-file={$backupDir}/backup.sql";

        $command = [
            "mysqldump",
            "--host",
            $databaseHost,
            "--port",
            $databasePort,
            "--user",
            $databaseUsername,
            "--password" . $databasePassword,
            $databaseName,
            "--databases",
            $fileTargetPath
        ];

        if ($databasePassword === "") {
            $command = [
                "mysqldump",
                "--host",
                $databaseHost,
                "--port",
                $databasePort,
                "--user",
                $databaseUsername,
                $databaseName,
                $fileTargetPath
            ];
        }

        $process = new Process($command);
        $process->setTimeout(90);
        $process->run();

        if ($process->isSuccessful() === false) {
            throw new ProcessFailedException($process);
        }

        $this->io->success("DATABASE BACK-UP CREATED");
        return Command::SUCCESS;
    }
}