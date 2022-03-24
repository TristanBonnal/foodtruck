<?php

namespace App\Models;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Gestion personnalisées des erreurs Json
 */
class JsonError
{
    private $error_code;
    private $messages;
    
    public function __construct(int $error_code = Response::HTTP_NOT_FOUND, string $message = "Not Found")
    {
        $this->error_code = $error_code;
        $this->messages[] = $message;
    }
    
    public function setValidationErrors(ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {
            $this->messages[] = $violation->getPropertyPath() . ': ' .  $violation->getMessage();
        }
    }

    /**
     * Get the value of error
     */ 
    public function getError()
    {
        return $this->error_code;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */ 
    public function setError($error_code)
    {
        $this->error_code = $error_code;

        return $this;
    }

    /**
     * Get the value of message
     */ 
    public function getMessage()
    {
        return $this->messages;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */ 
    public function setMessage($message)
    {
        $this->messages = $message;

        return $this;
    }
}