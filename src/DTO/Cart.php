<?php

namespace App\DTO;

use App\Entity\Product;

class Cart
{
    public array $products = [];
    public float $totalPrice = 0;

    public function addProduct(Product $product, $tvaAmount, $ttcPrice, $ttcTotal, int $quantity = 1): self
    {
        if (!isset($this->products[$product->getId()])) {

            $this->products[$product->getId()] = [
                'product' => $product,
                'quantity' => $quantity,
                'tva' => $tvaAmount,
                'ttcPrice' => $ttcPrice,
                'ttcTotal' => $ttcTotal,
            ];

        } else {
            $this->products[$product->getId()]['quantity'] += $quantity;
        }

        return $this;
    }

    public function setTotalPrice(float $totalTotal)
    {
        $this->totalPrice = $totalTotal;


    }
}