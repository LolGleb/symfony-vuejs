<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        $entityManager = $this->entityManager;
        $productList = $entityManager->getRepository(Product::class)->findAll();
//        dd($productList);
        return $this->render('main/default/index.html.twig', []);
    }

    #[Route('/product-add-old', name: 'product_add_old')]
    public function productAdd(): Response
    {
        $product = new Product();
        $product->setTitle('Product ' . rand(1, 100));
        $product->setDescription('smth');
        $product->setPrice(10);
        $product->setQuantity(1);

        $entityManager = $this->entityManager;
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->redirectToRoute('homepage');
    }

    #[Route('/product-edit/{id}', name: 'product_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Route('/product-add', name: 'product_add')]
    public function productEdit(Request $request, int $id = null): Response
    {
        $entityManager = $this->entityManager;
        if ($id) {
            $product = $entityManager->getRepository(Product::class)->find($id);
        } else {
            $product = new Product();
        }
        $form = $this->createFormBuilder($product)
        ->add('title', TextType::class)
        ->getForm();
//        dump($product->getTitle());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
//            dd($data);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }
//        dd($product, $form);
        return $this->render('main/default/product_edit.html.twig', ['form' => $form->createView()]);
    }
}
