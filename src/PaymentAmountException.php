<?php

namespace App;

class PaymentAmountException extends \Exception
{
    public function __construct($amountValue, $expectedAmountValue)
    {
        $message = sprintf("La valeur attendu %s est différente de la somme attendu %s", $amountValue, $expectedAmountValue);
        parent::__construct($message);
    }
}