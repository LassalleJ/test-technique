<?php

namespace App\Controller;

use App\DTO\Cart;
use App\Entity\Product;
use App\Entity\Vat;
use App\Events\CartEvent;
use App\Interfaces\CartServicesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(protected EntityManagerInterface   $entityManager,
                                protected EventDispatcherInterface $dispatcher,
                                protected CartServicesInterface    $cartServices,
                                protected RequestStack             $requestStack,
    )
    {
    }

    #[Route(path: '', name: 'index')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        /**
         * Si le panier existe en session, on le récupère,
         * si non, on crée un nouvel object panier et on le set en session
         */

        if ($session->get('cart') === null) {
            $cart = new Cart();

            $session->set('cart', $cart);
        } else {
            $cart = $session->get('cart');
        }

        /**
         * Récupérer les produits et leur quantité en session
         */

        foreach ($session->all() as $key => $product) {
            if (str_starts_with($key, 'product_') && !str_ends_with($key,'_quantity')) {
                $cart->addProduct(
                    $product,
                    $session->get($key . '_quantity') ?: 1
                );

            }
            if (str_starts_with($key, 'product_')) {
            $session->remove($key);
            }
        }
        $event = new CartEvent($cart, $this->cartServices, $this->entityManager);
        $this->dispatcher->dispatch($event, CartEvent::NAME);

        return $this->render('products/cart.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route(path: '/add', name: 'add')]
    public function addQuantity(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session = $this->requestStack->getMainRequest()->getSession();
        foreach ($session->all() as $key => &$product) {
            if (str_starts_with($key, 'product_' . $request->get('id'))) {
                $product['product']['quantity'] = $request->get('quantity');

            }
        }
    }

    #[Route(path: '/remove/{id}', name: 'remove')]
    public function removeProduct(Product $product, Request $request)
    {
        $session = $request->getSession();
        $cart = $session->get('cart');

        /**
         * Retirer le produit du panier
         */

        $cart->removeProductFromCart($product);
        return $this->redirectToRoute('cart_index');
    }

    #[Route(path: '/empty', name: 'empty')]
    public function emptyCart(Request $request)
    {
        $session = $request->getSession();

        /**
         * Vider le panier sans détruire complètement la session
         */

        $session->remove('cart');
        return $this->redirectToRoute('cart_index');
    }



}