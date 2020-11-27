<?php 

namespace App\ComponentInterface\Factory;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\ComponentInterface\Repos\CartRepositoryInterface;
use App\ComponentInterface\Repos\CartItemRepositoryInterface;
use App\ComponentInterface\Cart\OrderCartInterface;
use App\Entity\OrderCart;
use App\Entity\Cart;
use App\ComponentInterface\Cart\CartInterface;
use App\ComponentInterface\Product\ProductInterface;
use App\ComponentInterface\CartItem\CartItemInterface;
use App\Entity\CartItem;
use App\ComponentInterface\Cart\WishlistCartInterface;
use App\Entity\WishlistCart;

use App\ComponentInterface\CustomException\NullUserException;
use App\ComponentInterface\CustomException\CartHasProductException;


/** abstract class that defines basic objects and functionalities (by implementing CartFactoryInterface) for any cart */
abstract class CartFactory implements CartFactoryInterface{

    //class responsibile for handling basic cart actions like: addProducts to the cart, edit Cart, clear Cart, etc.

    /**
     * authenticated user
     * @var User 
     */
    protected $user;

    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    // /**
    //  * @var CartRepository
    //  */
    // private $cartRepository;

    /* autowire / dependency inject needed objects */
    public function __construct(EntityManagerInterface $entityManager, Security $security, CartRepositoryInterface $cartRepository, CartItemRepositoryInterface $cartItemRepository)
    {
        //set factory components
        $this->entityManager = $entityManager;
        $this->user = $security->getUser();

        //polymorphism, this can be any child of CartRepositoryInterface or CartItemRepositoryInterface
        //so we can implement same functions with different functionalities according to class type
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        
    }


    /**
     * {@inheritdoc}
     */
    public function instantiateCart(string $cartType): CartInterface{
        if(!$this->user) {
            $this->cart = null;
            return new Cart();
        }

        //instantiateCart //factory method design pattern
        //if user has cart retrieve it from DB, else create one
        //we have cartRepository instance, so it will poly. get the right card
        $this->cart = $this->cartRepository->findCartByUser($this->user);
        if(!$this->cart) {
            $this->cart = $this->getCardInstance($cartType);
            $this->user->addCart($this->cart);

            //persist new cart to DB
            $this->entityManager->persist($this->cart);
            $this->entityManager->flush();
        }

        // dump($this->cart); die;
        return $this->cart;
    }

    /**
     * small factory function to instantiate Cart based on cartType
     */
    private function getCardInstance($cartType){

        $cart = null;
        //add instantiation condition for each cart type
        if($cartType === OrderCartInterface::class || $cartType === OrderCart::class){

            $cart = new OrderCart();
            $cart->setItemsNumber(0);
            $cart->setTotalPrice(0);

        }else if($cartType === WishlistCartInterface::class || $cartType === WishlistCart::class){

            $cart = new WishlistCart();
            $cart->setItemsNumber(0);

        }else return new Cart();  //else return base cart

        
        return $cart;
    }


    /**
     * {@inheritdoc}
     */
    public function addProduct(ProductInterface $product, CartItemInterface $cartItem = null): CartInterface
    {
        //user may be not logged in, so it may be null
        if(!$this->user) throw new NullUserException("user not found!");

        //to add product, first check CartItem
        if(!$cartItem) $cartItem = new CartItem();  //use default cartItem

        if($this->hasProduct($product))
            throw new CartHasProductException($product);

        $cartItem->setProduct($product);  //product relashion established
        $this->cart->addItem($cartItem);  //cart relashion established, we can $orderCartItem->setCart($this->cart) too but this is more readable

        $this->cart->handleInnerStuffBeforePersist();  //update cart items number and other stuff dependent on cart type, need to be persisted

        //persist changes to DB
        $this->entityManager->persist($cartItem);
        $this->entityManager->persist($this->cart);

        $this->entityManager->flush();

        return $this->cart;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product): CartInterface
    {
        //get OrderCartItem relate between cart and product and remove it
        // foreach($this->cart->getItems() as $item)
        //     /** @var OrderCartItem $item */
        //     if($item->getProduct() === $product){
        //         // $this->cart->removeItem($item);  //OrderCartItem need to be persisted
        //         // $this->entityManager->persist($item);  //persist item will remove it from DB becasue orphanRemoval=true, or we can simply remove cart item using $entityManager->remove()
        //         // $this->entityManager->flush();

        //         $this->removeCartItemAndPersistChanges($item);

        //         $this->entityManager->flush();
        //     }

        //this can be done also using OrderCartItemRepo instead of looping through all cart items to get the one refer to this product, we can simply make
        //a function that select this OrderCartItem based on cart id and product id, but we have to inject OrderCartItemRepo to constructor so any will work anyway
        //you can see above techniqe too, both are okay

        //get cartIten relate between cart and product
        $cartItem = $this->cartItemRepository->findCartItemByCartIdAndProductId(
            $this->cart->getId(),
            $product->getId()
        );

        $this->cart->handleInnerStuffBeforePersist();  //update cart items number and other stuff dependent on cart type, need to be persisted        

        //persist changes to DB
        $this->removeCartItemAndPersistChanges($cartItem);
        $this->entityManager->persist($this->cart);
        
        $this->entityManager->flush();

        return $this->cart;
    }

    /**
     * {@inheritdoc}
     */
    public function clearCart(): CartInterface
    {
        //loop over cart items and remove them from cart, persist and finnaly flush to remove them from DB
        //again persist when CartItem be orphan (has no parent :'( ) the persist will remove it completely from DB since orphanRemoval=true
        foreach($this->cart->getItems() as $item)
            $this->removeCartItemAndPersistChanges($item);
      
        $this->cart->handleInnerStuffBeforePersist();  //update cart items number and other stuff dependent on cart type, need to be persisted        

        //persist changes to DB
        $this->entityManager->persist($this->cart);        
        $this->entityManager->flush();
        

        return $this->cart;

    }



    /**
     * {@inheritdoc}
     */
    public function hasProduct(ProductInterface $product): bool
    {
        //loop through cart products and check if this product exists
        //or i can select OrderCartItem based on cart id and product id and check if there is a result or null
        foreach($this->cart->getItems() as $item)
            if($item->getProduct() === $product) return true;

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function cartProducts()
    {
        //user may be not logged in, so it may be null
        if(!$this->user) throw new NullUserException("user not found!");

        //get products of $this->cart
        return $this->cartItemRepository->findProductsWithCartId($this->cart->getId());
    }

    /**
     * private inline method to remove cartitem and persist change to DB
     * @param OrderCartItem
     * @return 
     */
    private function removeCartItemAndPersistChanges($item){
        $this->cart->removeItem($item);  //OrderCartItem need to be persisted
        $this->entityManager->persist($item);  //persist item will remove it from DB becasue orphanRemoval=true, or we can simply remove cart item using $entityManager->remove()
    }

    /**
     * getter for factory Cart object
     * @return CartInterface
     */
    public function getFactoryCart(){
        return $this->cart;
    }

    public function getUser(){
        return $this->user;
    }

    //user mannually assigned so we need to instantiate it's cart
    public function setUser($user, string $cartType){

        $this->user = $user;

        //instantiate user cart
        $this->instantiateCart($cartType);

        return $this->user;
    }

}