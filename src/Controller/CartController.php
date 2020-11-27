<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;




class CartController extends AbstractController
{
    /**
     * @Route("/cart/{slug}/add", name="cart_add")
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function addProduct(Request $request,Product $product):RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Authorize first!');


        $manager = $this->getDoctrine()->getManager();

        $usr = $this->getUser();

        $user=$this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$usr]);

        $cart=$this->getDoctrine()->getRepository(Cart::class)->findOneBy(['user'=>$user]);

        $product=$request->get('product');

        if (!$cart){
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $cart->setDateCreated(new \DateTime());
            $manager->persist($cart);
            $manager->flush();
        }

        $cp = $this->getDoctrine()->getRepository(CartProduct::class)->findOneBy([
                    'cart' => $cart,
                    'product' => $product
                ]);

                if (!$cp) {
                    $cp = new CartProduct();
                    $cp->setCart($cart);
                    $cp->setProduct($product);
                    $cp->setQuantity(1);
                } else {
                    if($product->getQuantity()<=$cp->getQuantity()){
                        $this->addFlash("error", "unable to add product");
                        return $this->redirectToRoute('cart_list');
                    }else {
                        $cp->setQuantity($cp->getQuantity() + 1);
                    }
                }


                $manager->persist($cp);
        $manager->flush();


        $this->addFlash("addCart", "The product was successfully added to your cart",$request);

        return $this->redirectToRoute('cart_list');
    }


    /**
     * @Route("/cart/list", name="cart_list")
     */
    public function listAction()
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Authorize first!');

        $user=$this->getUser();

        $cart = $this->getDoctrine()
            ->getRepository(Cart::class)
            ->findOneBy(['user'=>$user]);

        $cart_product = $this->getDoctrine()
            ->getRepository(CartProduct::class)
            ->findBy(['cart'=>$cart]);

        return $this->render("cart/list.html.twig",
            [
                "cart" => $cart,
                "cart_product" => $cart_product,
                "user" => $user
            ]
        );
    }


    /**
     * @Route("/cart/increase/{id}", name="increase_cart_product_process")
     * @param CartProduct $cartProduct
     * @return Response
     */

    public function increaseProduct(CartProduct $cartProduct)
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Authorize first!');

        $em = $this->getDoctrine()->getManager();

        if ($cartProduct->getQuantity()>=$cartProduct->getProduct()->getQuantity()){
            return $this->redirectToRoute("cart_list");
        }else{
                $cartProduct->setQuantity($cartProduct->getQuantity()+1);
                $em->persist($cartProduct);
                $em->flush();

            }

        return $this->redirectToRoute("cart_list");
    }

    /**
     * @Route("/cart/decrease/{id}", name="decrease_cart_product_process")
     * @param CartProduct $cartProduct
     * @return Response
     */
    public function decreaseProduct(CartProduct $cartProduct)
    {

        $em = $this->getDoctrine()->getManager();


        if ($cartProduct->getQuantity()>1){
            $cartProduct->setQuantity($cartProduct->getQuantity()-1);
            $em->persist($cartProduct);
            $em->flush();

        }else {
            $em->remove($cartProduct);
            $em->flush();
        }

        return $this->redirectToRoute("cart_list");
    }



    /**
     * @Route("/cart/remove/{id}", name="remove_cart_product_process")
     * @param CartProduct $cartProduct
     * @return Response
     */
    public function removeProduct(CartProduct $cartProduct)
    {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Authorize first!');

        $em = $this->getDoctrine()->getManager();
            $em->remove($cartProduct);
            $em->flush();

        $this->addFlash("remove", "The product was successfully removed from your cart");

        return $this->redirectToRoute("cart_list");
    }

    /**
     * @Route("/cart/checkout", name="cart_checkout")
     * @return Response
     */
    public function checkOutCart()
    {

        $this->addFlash("checkOut", "Your order is being processed");

        return $this->render("cart/checkout.html.twig");


    }
}
