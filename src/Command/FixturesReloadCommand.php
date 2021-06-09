<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesReloadCommand extends Command
{
    protected static $defaultName = 'app:load-fixture';

    /** @var string */
    protected static $defaultDescription = 'Drop database and recreate it with schema and load fixtures';

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
            ->addArgument(
                'group-fixture',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'The group fixture to load'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Drop database then create Database with schema and load fixtures');

        $arguments= [];
        if (is_array($input->getArgument('group-fixture'))) {
            $arguments = $input->getArgument('group-fixture');
        }
        $this->runSymfonyCommand('doctrine:database:drop', ["--force" => true]);
        $this->runSymfonyCommand('doctrine:database:create', ["--if-not-exists" => true]);
        $this->runSymfonyCommand('doctrine:migrations:migrate', ["--no-interaction" => true]);
        $this->runSymfonyCommand( 'doctrine:fixtures:load', ["--no-interaction" => true], $arguments);

        $io->success('Fixtures load');

        return Command::SUCCESS;
    }

    /**
     * @param string $command
     * @param array<string, boolean> $options
     * @param string[] $arguments
     */
    private function runSymfonyCommand(
        string $command,
        array $options = [],
        array $arguments = []
    ): void {
        $application = $this->getApplication();

        if (!$application) {
            throw new LogicException('No application...');
        }

        $application->setAutoExit(false);

        $options["command"] = $command;

        if (count($arguments) > 0) {
            $options["--group"] = $arguments;
        }

        try {
            $application->run(new ArrayInput($options));
        } catch (Exception $exception) {
            throw new LogicException($exception->getMessage());
        }

    }
}
