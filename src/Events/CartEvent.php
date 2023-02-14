<?php

namespace App\Events;

use App\DTO\Cart;
use App\Entity\Vat;
use App\Interfaces\CartServicesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class CartEvent extends Event
{
    public const NAME = 'negocian.cart.dispatcher';

    public function __construct(protected Cart                   $cart,
                                protected ?CartServicesInterface $cartServices,
                                protected EntityManagerInterface $entityManager
    )
    {
        foreach ($this->cart->products as &$product) {
            $tvaAmount = $this->entityManager->getRepository(Vat::class)->findOneBy(['id' => $product['product']->getVat()])->getAmount();
            $product['ttcPrice'] = $this->cartServices->calculateTTC($product['product'], $tvaAmount);
            $product['tva'] = $tvaAmount;
            $product['ttcTotal'] = $this->cartServices->calculateTotal($product['product'], $tvaAmount, $product['quantity']);
        }
        $this->cart->setTotalPrice($this->cartServices->calculateFinalTotal($this->cart));
    }
}