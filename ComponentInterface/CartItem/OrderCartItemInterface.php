<?php

namespace App\ComponentInterface\CartItem;

interface OrderCartItemInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return int
     */
    public function getQuantity(): ?int;

    /**
     * @param int
     * @return OrderItemInterface
     */
    public function setQuantity(int $quantity): self;

    /**
     * @return float
     */
    public function getTotalPrice(): ?float;

    /**
     * @param float
     * @return OrderItemInterface
     */
    public function setTotalPrice(float $total_price): self;


    /**
     * calculate OrderCartItem total_price based on product price and quantity, NOTE: you need to persist OrderCartItem to update DB
     * @return float
     */
    public function calculatetotalPrice(): ?float;

}