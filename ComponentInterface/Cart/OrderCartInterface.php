<?php

namespace App\ComponentInterface\Cart;


interface OrderCartInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return float
     */
    public function getTotalPrice(): ?float;

    /**
     * @param float $total_price
     * @return OrderCartInterface
     */
    public function setTotalPrice(float $total_price): self;

    /**
     * calculate and store whole order price, NOTE: you need to persist OrderCard Object to update total price in DB
     * @return float
     */    
    public function calculateTotalPrice(): ?float;

}