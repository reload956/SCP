<?php

namespace App\ComponentInterface\Cart;

use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use App\ComponentInterface\CartItem\CartItemInterface;

interface CartInterface{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return int
     */
    public function getItemsNumber(): ?int;

    /**
     * @param int $items_number
     * @return CartInterface
     */
    public function setItemsNumber(int $items_number): self;

    /**
     * @return User
     */
    public function getUser(): ?User;

    /**
     * @param User $user
     * @return CartInterface
     */
    public function setUser(?User $user): self;

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection;

    /**
     * @param ItemInterface $item
     * @return CartInterface
     */
    public function addItem(CartItemInterface $item): self;

    /**
     * @param ItemInterface $item
     * @return CartInterface
     */
    public function removeItem(CartItemInterface $item): self;

    /**
     * calculate offer items number, NOTE: you need to persist Cart Object to update items number in DB
     * @return int
     */
    public function calculateItemsNumber(): ?int;

    /**
     * @param int $instancesNumber
     * @return int
     */
    public function increaseItemsNumber(int $instancesNumber): ?int;

    /**
     * @param int $instancesNumber
     * @return int
     */
    public function decreaseItemsNumber(int $instancesNumber): ?int;

    /**
     * handle any inner stuff of cart when it about to persisted to DB
     */
    public function handleInnerStuffBeforePersist();

}