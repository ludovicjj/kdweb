<?php

namespace App\Utils;


use Symfony\Component\Console\Exception\InvalidArgumentException;

class UserValidatorForCommand
{
    /**
     * Validate the entered email by user from CLI
     *
     * @param string|null $emailEntered
     * @return string
     */
    public function ValidateEmail(?string $emailEntered): string
    {
        if (empty($emailEntered)) {
            throw new InvalidArgumentException("YOU MUST PROVIDE USER EMAIL.");
        }

        if (!filter_var($emailEntered, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("USER EMAIL IS INVALID.");
        }

        [, $domain] = explode('@', $emailEntered);
        if (!checkdnsrr($domain)) {
            throw new InvalidArgumentException("USER EMAIL IS INVALID.");
        }

        return $emailEntered;
    }

    /**
     * Validate the entered password by user from CLI
     *
     * @param string|null $plainPassword
     * @return string
     */
    public function ValidatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException("YOU MUST PROVIDER USER PASSWORD.");
        }

        $passwordRegex = '/^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$/';
        if (!preg_match($passwordRegex, $plainPassword)) {
            throw new InvalidArgumentException(
                "PASSWORD MUST CONTAIN MIN 8 CHARACTERS WITH ONE CAP, ONE NUMBER AND ONE SPECIAL CHARACTER."
            );
        }

        return $plainPassword;
    }

    /**
     * Validate the entered email by user from CLI
     *
     * @param string|null $emailEntered
     * @return string
     */
    public function checkEmailForUserDelete(?string $emailEntered): string
    {
        if (empty($emailEntered)) {
            throw new InvalidArgumentException("YOU MUST PROVIDE USER EMAIL.");
        }

        if (!filter_var($emailEntered, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("ENTERED EMAIL IS INVALID.");
        }

        return $emailEntered;
    }
}