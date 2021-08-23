<?php

namespace App\Tests;

use App\Entity\User;
use DateTimeImmutable;
use DateInterval;
use Exception;

trait TestTrait
{
    private function truncateTable(string $table): void
    {
        try {
            $this->entityManager->getConnection()->executeQuery("TRUNCATE TABLE `{$table}`");
            $this->entityManager->getConnection()->close();
        } catch (Exception $exception) {

        }
    }

    private function createNewUserInDatabase(string $email, string $password, bool $isVerified = false): void
    {
        $user = (new User())
            ->setEmail($email)
            ->setPassword($password)
            ->setIsVerified($isVerified);

        if (!$isVerified) {
            $user->setAccountMustBeVerifiedBefore((new DateTimeImmutable())->add(new DateInterval("P1D")));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}