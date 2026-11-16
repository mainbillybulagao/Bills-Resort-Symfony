<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CUSTOMER')]
final class CustomerController extends AbstractController
{
    #[Route('/customer/dashboard', name: 'app_customer_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('customer/dashboard.html.twig', [
            'customer' => $this->getUser(),
        ]);
    }
}
