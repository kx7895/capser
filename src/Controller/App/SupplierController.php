<?php

namespace App\Controller\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/supplier', name: 'app_supplier_')]
class SupplierController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('app/layout/betatest.html.twig');
    }

}