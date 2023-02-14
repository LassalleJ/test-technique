<?php

namespace App\Controller;

use App\DTO\Cart;
use App\Entity\Product;
use App\Events\CartEvent;
use App\Interfaces\CartServicesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(protected EntityManagerInterface   $entityManager,
                                protected EventDispatcherInterface $dispatcher,
                                protected CartServicesInterface    $cartServices
    ) { }

    #[Route(path: '', name: 'index')]
    public function index(Request $request): Response
    {
        $cart = new Cart();
        /**
         * Récupérer les produits en session
         */
        $session = $request->getSession();
        foreach($session->all() as $key=>$product) {
            if (str_starts_with($key, 'product_')) {
                $cart->addProduct($product);
            }
        }

        $event = new CartEvent($cart, $this->cartServices);

        $this->dispatcher->dispatch($event, CartEvent::NAME);

        return $this->render('products/cart.html.twig', [
            'cart' => $cart,
        ]);
    }
}