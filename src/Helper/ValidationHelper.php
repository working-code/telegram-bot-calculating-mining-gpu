<?php
declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationHelper
{
    public function getErrorMessageByErrors(ConstraintViolationListInterface $errors): string
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
