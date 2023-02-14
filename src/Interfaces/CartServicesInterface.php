<?php

namespace App\Interfaces;

use App\DTO\Cart;
use App\Entity\Product;
use App\Entity\Vat;

interface CartServicesInterface
{
    public function calculateTTC(Product $product, float $tva): float;

    public function calculateTotal(Product $product, float $tva, int $quantity): float;

    public function calculateFinalTotal(Cart $cart): float;
}