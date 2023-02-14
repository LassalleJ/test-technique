<?php

namespace App\Services;

use App\DTO\Cart;
use App\Entity\Product;
use App\Entity\Vat;
use App\Interfaces\CartServicesInterface;

class CartServices implements CartServicesInterface
{
    public function calculateTTC(Product $product, float $tva): float
    {
        $HTprice = $product->getPriceHT();
        return $HTprice + ($HTprice * $tva /100);
    }

    public function calculateTotal(Product $product, float $tva, int $quantity): float
    {
        $HTprice = $product->getPriceHT();
        return ($HTprice + ($HTprice * $tva /100)) * $quantity;
    }

    public function calculateFinalTotal(Cart $cart): float
    {
        $totalTotal=0;
        $products=$cart->products;
        foreach ($products as $product) {
            $totalTotal = $totalTotal + $product['ttcTotal'];
        }
        return $totalTotal;
    }
}