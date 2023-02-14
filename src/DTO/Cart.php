<?php

namespace App\DTO;

use App\Entity\Product;

class Cart
{
    public array $products = [];
    public float $totalPrice = 0;

    public function addProduct(Product $product, int $quantity = 1): self
    {
        if (!isset($this->products[$product->getId()])) {

            $this->products[$product->getId()] = [
                'product' => $product,
                'quantity' => $quantity,
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

    public function removeProductFromCart(Product $product)
    {
        foreach ($this->products as $productId => $productInCart) {

            if ($productInCart['product']->getId() == $product->getId()) {
                unset($this->products[$productId]);
            }
        }

    }
}