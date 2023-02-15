<?php

namespace App\Controller;

use App\DTO\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/products', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '', name: 'index')]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)
            ->findActiveProducts();

        return $this->render('products/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route(path: '/add/{id}', name: 'add')]
    public function addProductToCart(Request $request, Product $product): Response
    {
        $total=0;
        $session=$request->getSession();

        /**
         * On stocke en session le produit ajouté ainsi que sa quantité.
         * Si l'utilisateur clique deux fois sur le bouton ajouter produit,
         * la quantité du produit augmente de 1 en session
         */

        if($session->get('product_' . $product->getId()) === null) {
            $session->set('product_' . $product->getId(), $product);
            $session->set('product_'. $product->getId() . '_quantity', 1);
        } else {
            $currentQuantity = $session->get('product_' . $product->getId() . '_quantity');
            $newQuantity = $currentQuantity + 1;
            $session->set('product_'. $product->getId() . '_quantity', $newQuantity);
        }

        /**
         * Calcul du nombre total de produits dans le panier
         * (on prend en compte les produits dans l'objet panier, ainsi que les produits encore en session)
         */

        if($session->get('cart') !== null) {
            $productsInCart = $session->get('cart')->products;
        }

        if(isset($productsInCart) && !empty($productsInCart)) {
            foreach($productsInCart as $productInCart) {
                $total += $productInCart['quantity'];
            }
        }
        foreach($session->all() as $key => $value) {
            if (str_ends_with($key, '_quantity')) {
                $total += $value;
            }
        }
        return $this->json(['total'=>$total]);
    }

}