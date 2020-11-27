<?php

namespace App\ComponentInterface\CartItem;

use App\ComponentInterface\Cart\CartInterface;
use App\ComponentInterface\Product\ProductInterface;

interface CartItemInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return CartInterface
     */
    public function getCart(): ?CartInterface;

    /**
     * @param CartInterface
     * @return ItemInterface
     */
    public function setCart(?CartInterface $cart): self;

    /**
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface;

    /**
     * @param ProductInterface
     * @return ItemInterface
     */
    public function setProduct(?ProductInterface $product): self;

}