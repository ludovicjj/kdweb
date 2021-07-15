<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CreateUsersFromFileCommand extends Command
{
    protected static $defaultName = 'app:create-users-from-file';
    protected static $defaultDescription = 'Add a short description for your command';

    /** @var SymfonyStyle $io */
    private $io;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var string $dataDir */
    private $dataDir;

    /** @var UserRepository $userRepository */
    private $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $dataDir,
        UserRepository $userRepository,
        string $name = null
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->dataDir = $dataDir;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createUsers();

        return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDir . '/random-user.xml';
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $normalizers = [new ObjectNormalizer()];
        $encoders = [
            new CsvEncoder(),
            new YamlEncoder(),
            new XmlEncoder()
        ];
        $serializer = new Serializer($normalizers, $encoders);
        /** @var string $fileContent */
        $fileContent = file_get_contents($file);
        $data = $serializer->decode($fileContent, $fileExtension);

        if (array_key_exists('results', $data)) {
            return $data['results'];
        }

        return $data;
    }

    private function createUsers(): void
    {
        $this->io->section("CREATE USERS FROM FILE.");
        $usersCreated = 0;
        foreach ($this->getDataFromFile() as $row) {
            if (array_key_exists('email', $row) && !empty($row['email'])) {
                $user = $this->userRepository->findOneBy(['email' => $row['email']]);

                if (!$user) {
                    $user = new User();
                    $user
                        ->setEmail($row['email'])
                        ->setPassword("secret")
                        ->setIsVerified(true);
                    $this->entityManager->persist($user);
                    $usersCreated++;
                }
            }
        }
        $this->entityManager->flush();

        if ($usersCreated > 1) {
            $message = "{$usersCreated} USERS HAVE BEEN CREATED IN DATABASE";
        } elseif ($usersCreated === 1) {
            $message = "ONE USER HAVE BEEN CREATED IN DATABASE";
        } else {
            $message = "NO USER HAVE BEEN CREATED IN DATABASE";
        }

        $this->io->success($message);
    }
}
