<?php


namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordDTO
{
    /**
     * @var string|null $email
     *
     * @Assert\NotBlank(
     *     message="Le champs email ne peut être vide."
     * )
     * @Assert\Email(
     *     message="'{{ value }}' n'est pas une adresse email valide."
     * )
     */
    private $email;

    public function __construct(
        ?string $email
    )
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}