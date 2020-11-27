<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $em = $this->getDoctrine()->getManager();

        $user=$this->getUser();

        $OrderRepository = $em->getRepository(Order::class);

        $OrderQuery=$OrderRepository->createQueryBuilder('p')
            ->where("p.user == $user")
            ->getQuery();
        $CategoryRepository=$em->getRepository(Category::class);
        $CategoryQuery=$CategoryRepository->findAll();

        $Products=$paginator->paginate($OrderQuery,$request->query

            ->getInt('page',1),MaxPage);


        return $this->render('product/index.html.twig', [
            'products' => $Products,
            'categories'=>$CategoryQuery]);
    }
}
