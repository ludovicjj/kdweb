<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordDTO
{
    /**
     * @var string|null $password
     *
     * @Assert\NotBlank(
     *     message="Le mot de passe ne peut pas être vide."
     * )
     * @Assert\Regex(
     *     pattern="/^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$/",
     *     message="Votre mot de passe doit contenir au minimum 8 caractères avec au moins une majuscule, un chiffre et un caractère spécial."
     * )
     * @Assert\Length(
     *     max=255,
     *     maxMessage="Votre mot de passe dépasse la limite de caractères accepté."
     * )
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