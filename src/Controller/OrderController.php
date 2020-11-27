<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Delivery;
use App\Entity\Order;
use App\Entity\User;
use App\Form\DeliveryType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
@Route("/product")
 */
const Max_Page=10;
class OrderController extends AbstractController
{

    /**
     * @Route("/orders", methods="GET", name="orders_list")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */

    public function index(Request $request, PaginatorInterface $paginator): Response
    {

        $user=$this->getUser();

        $order = $this->getDoctrine()
            ->getRepository(Order::class)
            ->findBy(['user'=>$user]);


        $Orders=$paginator->paginate($order,$request->query

            ->getInt('page',1),Max_Page);


        return $this->render('order/index.html.twig', [
            'orders' => $Orders]);
    }

    /**
     * @Route("/order/new", methods="GET", name="order_new")
     */
    public function new(Request $request){
        $user=$this->getUser();
        $cart=$this->getDoctrine()
            ->getRepository(Cart::class)
            ->findBy(['user'=>$user]);

        $cart_product = $this->getDoctrine()
            ->getRepository(CartProduct::class)
            ->findBy(['cart'=>$cart]);

        $totalprice=0;

        for ($i=1;$i<sizeof($cart_product);$i++){
            $totalprice+=$cart_product[$i]->getQuantity()*$cart_product[$i]->getProduct()->getPrice();
        }

        if(!$cart_product) {
            $this->addFlash("warning", "no products provided yet");
            return $this->redirectToRoute("product_index");}
        else{
            $manager = $this->getDoctrine()->getManager();
            $delivery = new Delivery();
            $form = $this->createForm(DeliveryType::class, $delivery);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($delivery);
                $manager->flush();

                $order=new Order();
                $order->setUser($this->getUser());
                $order->setDateCreated(new \DateTime());
                $order->setTotal($totalprice);
                $manager->persist($cart);
                $manager->flush();

                return $this->redirectToRoute("product_index");


            }
        }
        return $this->redirectToRoute("product_index");
    }
}
