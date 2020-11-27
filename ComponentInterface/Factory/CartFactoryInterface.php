<?php

namespace App\ComponentInterface\Factory;

use App\ComponentInterface\Cart\CartInterface;
use App\ComponentInterface\Product\ProductInterface;
use App\ComponentInterface\CartItem\CartItemInterface;

interface CartFactoryInterface{

    /**
     * factory method to instantiate Cart Object (factory method design pattern)
     * @param string $cartType
     * @return CartInterface
     */
    public function instantiateCart(string $cartType): CartInterface;

    /**
     * add given product to user's cart ( which cart? it's polymorphism baby!)
     * @param ProductInterface,CartItemInterface
     * @return CartInterface
     */
    public function addProduct(ProductInterface $product, CartItemInterface $cartItem = null): CartInterface;

    /**
     * remove given product from user's cart ( which cart? it's polymorphism baby!)
     * @param ProductInterface
     * @return CartInterface
     */
    public function removeProduct(ProductInterface $product): CartInterface;

    /**
     * clear user's cart, remove all products
     * @return CartInterface
     */
    public function clearCart(): CartInterface;

    /**
     * get all products assigned with the cart of this cartFactory
     * @return Array
     */
    public function cartProducts();

    /**
     * check if this OrderCart has this product or not
     * @param ProductInterface
     * @return bool
     */
    public function hasProduct(ProductInterface $product): bool;

}