<?php

namespace App\ComponentInterface\Repos;

use App\ComponentInterface\CartItem\CartItemInterface;

interface CartItemRepositoryInterface{

    /**
     * find all products belong to specific cart
     * @param int
     * @return 
     */
    public function findProductsWithCartId(int $cart_id);

    /**
     * get CartItem relate cart to product, which CartItem? this will be based on which child repo we instantiated, it's polymorphism :)
     * @param int,int
     * @return CartInterface
     */
    public function findCartItemByCartIdAndProductId(int $cart_id, int $product_id);

}