<?php
// src/Validator/IsFromWyllDomainValidator.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsFromWyllDomainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsFromWyllDomain) {
            throw new UnexpectedTypeException($constraint, IsFromWyllDomain::class);
        }

        $explodedEmail = explode('@', $value);
        $domain = array_pop($explodedEmail);

        if (!in_array($domain, $constraint->domains)) {
            $this->context->buildViolation($constraint->message)
                 ->setParameter('%email%', $value)
                 ->addViolation();
        }
    }
}