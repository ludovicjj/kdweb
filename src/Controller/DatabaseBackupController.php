<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class DatabaseBackupController extends AbstractController
{
    /** @var KernelInterface $kernel */
    private $kernel;

    /** @var string $projectDir */
    private $projectDir;

    public function __construct(
        KernelInterface $kernel,
        string $projectDir
    )
    {
        $this->kernel = $kernel;
        $this->projectDir = $projectDir;
    }

    /**
     * @Route("/database-back-up",
     *     name="database_back_up",
     *     methods={"GET"},
     *     defaults={
     *          "_public_access": false
     *     }
     * )
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $user = $this->getUser();

        if ($user === null) {
            throw new \LogicException("expected authenticated user");
        }

        return $this->render("database/index.html.twig");
    }

    /**
     * @Route("/send-database-back-up",
     *     name="send_database_back_up",
     *     methods={"POST"},
     *     defaults={
     *          "_public_access": false
     *     }
     * )
     */
    public function save()
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            "command" => "app:database-backup"
        ]);

        $output = new NullOutput();

        try {
            $application->run($input, $output);
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

        $backupFile = "{$this->projectDir}/var/backup/backup.sql";
        $dateTimeString = (new \DateTimeImmutable("now"))->format("d-m-Y-H-i-s");

        $backupFileRenamed = "{$this->projectDir}/var/backup/backup-{$dateTimeString}.sql";

        $fileSystem = new Filesystem();

        try {
            $fileSystem->rename(
                $backupFile,
                $backupFileRenamed
            );
        } catch (IOException $exception) {
            throw new IOException($exception);
        }

        if (file_exists($backupFileRenamed) === true) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($backupFileRenamed).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backupFileRenamed));

            while (ob_get_level())
            {
                ob_end_clean();
            }

            readfile($backupFileRenamed);
            exit;
        }
    }
}