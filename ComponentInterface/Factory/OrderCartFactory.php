<?php 

namespace App\ComponentInterface\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\OrderCartRepository;
use App\ComponentInterface\Product\ProductInterface;
use App\Entity\OrderCart;
use App\Entity\OrderCartItem;
use App\ComponentInterface\Cart\CartInterface;
use App\Repository\OrderCartItemRepository;
use App\ComponentInterface\CartItem\CartItemInterface;

class OrderCartFactory extends CartFactory{

    //class responsibile for handling cart actions like: addProducts to the cart, edit Cart, clear Cart, etc.

    /* autowire / dependency inject needed objects */
    public function __construct(OrderCartRepository $orderCartRepository, OrderCartItemRepository $orderCartItemRepository, EntityManagerInterface $entityManager, Security $security)
    {
        parent::__construct($entityManager, $security, $orderCartRepository, $orderCartItemRepository);
        $this->instantiateCart(OrderCart::class);  //important to instatiate Cart with your type
    }


    //implement contract (CartFactoryInterface) functionalities

    // /**
    //  * {@inheritdoc}
    //  */
    // public function instantiateCart()
    // {
    //     //if user has cart retrieve it from DB, else create one
    //     $this->cart = $this->orderCartRepository->findOrderCartByUser($this->user);
    //     if(!$this->cart) $this->cart = new OrderCart();

    //     return $this->cart;
    // }


    /**
     * add product with it's quantity to OrderCart, this is customization to parent method addProduct because OrderCart needs more params (quantity)
     * @param ProductInterface,int
     * @return CartInterface
     */
    public function addProduct(ProductInterface $product, CartItemInterface $cartItem = null): CartInterface
    {
        //to add product, first make OrderCartItem
        $quantity = 1; //first add product with quantity 1
        $cartItem = new OrderCartItem();  //need to be persisted, we have autowired entityManager :)
        $cartItem->setQuantity($quantity);
        $cartItem->setTotalPrice($product->getPaidPrice() * $quantity);  //can be done by calculateTotalPrice but after establish the product relation, or simply like this

        return parent::addProduct($product, $cartItem);
    }

    /**
     * edit product CartItem quantity and update items number and total price
     * @param ProductInterface
     * @return int
     */
    public function editItemQuantity(ProductInterface $product, int $quantity){

        //get CartItem that relate $this->cart to $product then update quantity
        $cartItem = $this->cartItemRepository->findCartItemByCartIdAndProductId(
            $this->cart->getId(),
            $product->getId()
        );
        
        if($product->hasInstances($quantity)){
            //set CartItem quantity
            $cartItem->setQuantity($quantity);
            //calculate new total price based on new quantity
            $cartItem->setTotalPrice($product->getPaidPrice() * $quantity);

            $this->cart->handleInnerStuffBeforePersist();  //update cart items number and price, need to be persisted            

            //persist CartItem changes
            $this->entityManager->persist($cartItem);
            $this->entityManager->persist($this->cart);
            $this->entityManager->flush();
        }

        return -1;
    }


}