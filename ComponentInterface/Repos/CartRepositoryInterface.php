<?php

namespace App\ComponentInterface\Repos;

use App\ComponentInterface\Cart\CartInterface;
use App\Entity\User;

interface CartRepositoryInterface{

    /**
     * get user specific card, which card? this will be based on which child repo we instantiated, it's polymorphism :)
     * @param $user
     * @return CartInterface
     */
    public function findCartByUser(User $user);

}