<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as Constraint;

class RegistrationDTO
{
    /**
     * @var string|null $email
     * @Assert\NotBlank(
     *     message="Le champs email ne peut être vide."
     * )
     * @Assert\Email(
     *     message="{{ value }} n'est pas une adresse email valide."
     * )
     * @Assert\Length(
     *     max=180,
     *     maxMessage="Votre adresse email dépasse la limite de caractères accepté."
     * )
     * @Constraint\EmailAvailable()
     */
    private $email;

    /**
     * @var string|null $password
     * @Assert\NotBlank(
     *     message="Le champs password ne peut être vide."
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
        ?string $email,
        ?string $password
    ) {
        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}