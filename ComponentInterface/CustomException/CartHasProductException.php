<?php

namespace App\ComponentInterface\CustomException; 
use App\ComponentInterface\Product\ProductInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Throwable;

class CartHasProductException extends Exception{

    public $product;

    public function __construct(ProductInterface $product, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->product = $product;
    }

}