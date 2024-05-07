<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationErrorException extends Exception
{
    public function __construct(
        readonly ConstraintViolationListInterface $violationList,
    ) {
        parent::__construct($this->getMessageFromConstraintViolationList($violationList));
    }

    private function getMessageFromConstraintViolationList(ConstraintViolationListInterface $errors): string
    {
        $errorMessage = '';

        if ($errors->count()) {
            foreach ($errors as $error) {
                $errorMessage .= sprintf("%s\n", $error->getMessage());
            }
        }

        return $errorMessage;
    }
}
