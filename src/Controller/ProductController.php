<?php

namespace App\Controller;

use App\Entity\Product;
use App\Event\CommentCreatedEvent;
use Symfony\Component\Form\FormError;
use App\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ProductController extends AbstractController
{
    /**
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods="GET", name="product_index")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods="GET", name="product_index_paginated")
     * @Cache(smaxage="10")
     */
    public function index(Request $request, int $page, string $_format, ProductRepository $products): Response
    {
        $tag = null;
        $latestProducts = $products->findLatest($page, $tag);

        return $this->render('product/index.html.twig', [
            'paginator' => $latestProducts,
            'tagName' => $tag ? $tag->getName() : null,
        ]);
    }

    /**
     * @Route("/product/{slug}", methods="GET", name="product_post")
     */
    public function postShow(Product $product): Response
    {
        return $this->render('product/product_show.html.twig', ['product' => $product]);
    }

    /**
     * @Route("/comment/{productSlug}/new", methods="POST", name="comment_new")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @ParamConverter("post", options={"mapping": {"productSlug": "slug"}})
     */
    public function commentNew(Request $request, Product $product, EventDispatcherInterface $eventDispatcher): Response
    {
        $comment = new Comment();
        $comment->setUserId($this->getUser());
        $product->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            return $this->redirectToRoute('product_info', ['slug' => $product->getSlug()]);
        }

        return $this->render('product/comment_form_error.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    public function commentForm(Product $product): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('product/_comment_form.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search", methods="GET", name="product_search")
     */
    public function search(Request $request, ProductRepository $product): Response
    {
        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);

        if (!$request->isXmlHttpRequest()) {
            return $this->render('product/search.html.twig', ['query' => $query]);
        }

        $foundProducts = $product->findBySearchQuery($query, $limit);

        $results = [];
        foreach ($foundProducts as $product) {
            $results[] = [
                'title' => htmlspecialchars($product->getName(), ENT_COMPAT | ENT_HTML5),
                'date' => $product->getCreatedAt()->format('M d, Y'),
                'author' => htmlspecialchars($product->getImage(), ENT_COMPAT | ENT_HTML5),
                'summary' => htmlspecialchars($product->getSummary(), ENT_COMPAT | ENT_HTML5),
                'url' => $this->generateUrl('product_info', ['slug' => $product->getSlug()]),
            ];
        }

        return $this->json($results);
    }
}


