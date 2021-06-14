<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class EmailAvailable
 * @package App\Validator\Constraints
 * @Annotation
 */
class EmailAvailable extends Constraint
{
    /** @var string $message */
    public $message = "Cette adresse email ne peut pas être utilisée.";

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}