<?php
// src/Validator/IsFromWyllDomain.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsFromWyllDomain extends Constraint
{
    public $domains = ["wyll.io"];
    public $message = 'The email "%email%" is not from Wyll.';
}