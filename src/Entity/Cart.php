<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 * @ORM\Table(name="cart")
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime",options={"default"= "CURRENT_TIMESTAMP"})
     */
    private $DateCreated;

    /**
     * @ORM\OneToMany(targetEntity=CartProduct::class, mappedBy="cart", orphanRemoval=true)
     */
    private $Product;

    /**
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cart")
     */
    private $user;

    public function __construct()
    {
        $this->Product = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->DateCreated;
    }

    public function setDateCreated(\DateTimeInterface $DateCreated): self
    {
        $this->DateCreated = $DateCreated;

        return $this;
    }

    /**
     * @return Collection|CartProduct[]
     */
    public function getProduct(): Collection
    {
        return $this->Product;
    }

    public function addProduct(CartProduct $product): self
    {
        if (!$this->Product->contains($product)) {
            $this->Product[] = $product;
            $product->setCart($this);
        }

        return $this;
    }

    public function removeProduct(CartProduct $product): self
    {
        if ($this->Product->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCart() === $this) {
                $product->setCart(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
