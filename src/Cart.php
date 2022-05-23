<?php

namespace App;

class Cart
{

    public function getProducts(): array
    {
        return [
            [
                "name" => "Peluche ration laveur",
                "price" => 1499
            ],
            [
                "name" => "Peluche panda roux",
                "price" => 2099
            ]
        ];
    }

    public function getTotal(): int
    {
        return array_reduce($this->getProducts(), function (int $carry, array $product) {
           return $carry += $product['price'];
        }, 0);
    }
}