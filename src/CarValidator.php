<?php

namespace App;

class CarValidator
{
    $errors = [];
    if (empty($car['make'])) {
        $errors['make'] = "Make can not be empty";
    }

    if (empty($car['model'])) {
        $errors['model'] = "Model can not be empty";
    }

    return $errors;
}