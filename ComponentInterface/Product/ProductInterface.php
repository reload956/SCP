<?php

namespace App\ComponentInterface\Product;

interface ProductInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getName(): ?string;
    

    /**
     * @param string $name
     * @return ProductInterface
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getDescription(): ?string;


    /**
     * @param string $description
     * @return ProductInterface
     */
    public function setDescription(?string $description): self;

    /**
     * @return float
     */
    public function getPrice(): ?float;

    /**
     * @param float $price
     * @return ProductInterface
     */
    public function setPrice(float $price): self;

    /**
     * @return int
     */
    public function getQuantity(): ?int;

    /**
     * @param int $quantity
     * @return ProductInterface
     */
    public function setQuantity(int $quantity): self;

    /**
     * @return string
     */
    public function getImage(): ?string;

    /**
     * @param string $image
     * @return ProductInterface
     */
    public function setImage(?string $image): self;

    /**
     * check if product have enough instance to order
     * @param int $instancesNumber
     * @return bool
     */
    public function hasInstances(int $instancesNumber): ?bool;

    /**
     * decrease item stock quantity with number of instances and return new product stock quantity, NOTE: you need to persist Product Object to update DB
     * @param int $instancesNumber
     * @return int new quantity or -1 if not enough quantity found
     */
    public function decreaseQuantity(int $instancesNumber): ?int;

    /**
     * increase item stock quantity with number of instances and return new product stock quantity, NOTE: you need to persist Product Object to update DB
     * @param int $instancesNumber
     * @return int new quantity
     */
    public function increaseQuantity(int $instancesNumber): ?int;
}