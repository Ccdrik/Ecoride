<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/{reactRouting}', name: 'app_default', priority: -1, requirements: ['reactRouting' => '^(?!api).+'])]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
