<?php

namespace App\Tests;

use App\DataFixtures\ProductFixtures;
use App\DataFixtures\VatFixtures;
use App\DTO\Cart;
use App\Entity\Product;
use App\Events\CartEvent;
use App\Services\CartServices;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CartServicesTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool  = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    #[NoReturn]
    public function testCalculateTTC(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->databaseTool->loadFixtures([
            VatFixtures::class,
            ProductFixtures::class
        ]);

        $product = $this->entityManager->getRepository(Product::class)
            ->findOneBy([]);
        $tva = $product->getVat()->getAmount();

        $cartServices  = $container->get(CartServices::class);
        $methodeResult = $cartServices->calculateTTC($product,$tva);

        $this->assertEquals(
            134.67,
            $methodeResult,
            'Le résultat attendu de la valeur TTC doit être 134.76, avez-vous fait attention aux décimales ?'
        );
    }

    #[NoReturn]
    public function testCalculateTotal(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->databaseTool->loadFixtures([
            VatFixtures::class,
            ProductFixtures::class
        ]);

        $product = $this->entityManager->getRepository(Product::class)
            ->findOneBy([]);
        $tva = $product->getVat()->getAmount();

        $cartServices  = $container->get(CartServices::class);
        $methodeResultQt3 = $cartServices->calculateTotal($product,$tva, 3);
        $methodeResultQt1 = $cartServices->calculateTotal($product,$tva, 1);

        $this->assertEquals(
            134.67,
            $methodeResultQt1,
            'Le résultat attendu de la valeur TTC doit être 134.67, avez-vous fait attention aux décimales et à la quantité ?'
        );

        $this->assertEquals(
            404.02,
            $methodeResultQt3,
            'Le résultat attendu de la valeur TTC doit être 404.02, avez-vous fait attention aux décimales et à la quantité ?'
        );
    }

    #[NoReturn]
    public function testCalculateFinalTotal(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->databaseTool->loadFixtures([
            VatFixtures::class,
            ProductFixtures::class
        ]);

        $cart = new Cart();

        for ($i=1 ; $i <= 3 ; $i++) {
            $product = $this->entityManager->getRepository(Product::class)
                ->findOneBy(['id' => $i]);
            $cart->addProduct($product);
        }

        $cartServices  = $container->get(CartServices::class);

        $event = new CartEvent($cart, $cartServices, $this->entityManager);
        $this->dispatcher->dispatch($event, CartEvent::NAME);

        $methodeResult = $cartServices->calculateFinalTotal($cart);

        $this->assertEquals(
            238.24,
            $methodeResult,
            'Le résultat attendu de la valeur TTC doit être 238.24, avez-vous fait attention aux décimales ?'
        );
    }
}