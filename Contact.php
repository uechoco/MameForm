<?php

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

class Contact
{
    public $name;
    public $email;
    public $message;

    // set validations
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank(array('message'=>'name is required.')));
        $metadata->addPropertyConstraint('email', new Constraints\NotBlank(array('message'=>'email is required.')));
        $metadata->addPropertyConstraint('email', new Constraints\Email(array('message'=>'email is not valid.')));
        $metadata->addPropertyConstraint('message', new Constraints\NotBlank(array('message'=>'message is required.')));
    }
}