<?php

namespace App\ComponentInterface\Product;

interface SaleProductInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return float
     */
    public function getSalePrice(): ?float;

    /**
     * @param float $sale_price
     * @return SaleProductInterface
     */
    public function setSalePrice(float $sale_price): self;

    /**
     * @return float
     */
    public function getDiscount(): ?float;

    /**
     * @param float $discount
     * @return SaleProductInterface
     */
    public function setDiscount(float $discount): self;


    /**
     * calculate SaleProduct discount based on price and sale_price, NOTE: you need to persist SaleProduct to update DB
     * @return float SaleProduct discount like: 70 (for 70% discount) 
     */
    public function calculateDiscount(): ?float;

}