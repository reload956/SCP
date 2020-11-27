<?php 

namespace App\ComponentInterface\Service;
use App\ComponentInterface\CustomException\ProductNotFoundException;
use App\Repository\ProductRepository;



class ProductService{

    private $productRepo;
    public function __construct(ProductRepository $productRepo){
        $this->productRepo = $productRepo;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAllProducts() {
        return $this->productRepo->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function findProductWithIdOrName($idOrName) {
        $product = $this->productRepo->findOneBy(["id" => $idOrName]);
        if(!$product) $product = $this->productRepo->findOneBy(["name" => $idOrName]);

        if(!$product) throw new ProductNotFoundException();

        return $product;
    }


}