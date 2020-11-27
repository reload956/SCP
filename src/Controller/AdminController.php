<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;




    /**
     * @Route("/Admin")
     * @IsGranted("ROLE_ADMIN")
     */
class AdminController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="admin_index")
     * @Route("/", methods="GET", name="admin_product_index")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {

        $em = $this->getDoctrine()->getManager();

        $ProductRepository = $em->getRepository(Product::class);

        $ProductsQuery=$ProductRepository->createQueryBuilder('p')
            ->getQuery();

        $Products=$paginator->paginate($ProductsQuery,$request->query

            ->getInt('page',1),10);

        return $this->render('admin/product/index.html.twig', ['products' => $Products]);
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", methods="GET|POST", name="admin_product_new")
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function new(Request $request): Response
    {



        $product = new Product();
        // See https://symfony.com/doc/current/form/multiple_buttons.html
        $form = $this->createForm(ProductType::class, $product)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid()){
            /** @var UploadedFile $file */
            $product->setCreatedAt(new \DateTime());
            $file = $form->get('image_form')->getData();
            if (!$file) {
                $form->get('image_form')->addError(new FormError('Image is required'));
            } else {
                $filename = md5($product->getName() . '' . $product->getCreatedAt()->format("Y-m-d H:i:s"));

                $file->move($this->getParameter('brochures_directory'),
                    $filename
                );

                $product->setImage($filename);
            }
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                // Flash messages are used to notify the user about the result of the
                // actions. They are deleted automatically from the session as soon
                // as they are accessed.
                // See https://symfony.com/doc/current/controller.html#flash-messages
                $this->addFlash('success', 'product added successfully');

                if ($form->get('saveAndCreateNew')->isClicked()) {
                    return $this->redirectToRoute('admin_product_new');
                }

                return $this->redirectToRoute('admin_product_index');
            }
        return $this->render('admin/product/new.html.twig', [
            'products' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id<\d+>}", methods="GET", name="admin_product_show")
     */
    public function show(Product $product): Response
    {
        // This security check can also be performed
        // using an annotation: @IsGranted("show", subject="post", message="Posts can only be shown to their authors.")


        return $this->render('admin/product/show.html.twig', [
            'products' => $product,
        ]);
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_product_edit")
     * @IsGranted("edit", subject="post", message="Posts can only be edited by their authors.")
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'post' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}/delete", methods="POST", name="admin_post_delete")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Request $request, Product $product): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_product_index');
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $product->getTags()->clear();

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'product.deleted_successfully');

        return $this->redirectToRoute('admin_product_index');
    }

}

