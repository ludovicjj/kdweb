<?php

namespace App\DTO;

class ResetPasswordDTO
{
    /**
     * @var string|null $password
     */
    private $password;

    public function __construct(
        ?string $password
    )
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}