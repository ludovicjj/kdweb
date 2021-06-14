<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EmailAvailableValidator extends  ConstraintValidator
{
    /**
     * @var UserRepository $userRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailAvailable) {
            throw new UnexpectedTypeException($constraint, EmailAvailable::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->isValideEmail($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();
        }
    }

    private function isValideEmail(string $value): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $value]);

        return is_null($user);
    }
}