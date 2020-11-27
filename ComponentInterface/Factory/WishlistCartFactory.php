<?php 

namespace App\ComponentInterface\Factory;

use App\Repository\WishlistCartRepository;
use App\Repository\CartItemRepository;
use App\Entity\WishlistCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class WishlistCartFactory extends CartFactory{

    //class responsibile for handling cart actions like: addProducts to the cart, edit Cart, clear Cart, etc.

    /* autowire / dependency inject needed objects */
    public function __construct(WishlistCartRepository $wishlistCartRepository, CartItemRepository $cartItemRepository, EntityManagerInterface $entityManager, Security $security)
    {
        parent::__construct($entityManager, $security, $wishlistCartRepository, $cartItemRepository);
        $this->instantiateCart(WishlistCart::class);  //important to instatiate Cart with your type, factory method design pattern
    }


    //no need to additional specific factory functionalities for wishlistCart, it will need only the basic parent functionalities
    

}