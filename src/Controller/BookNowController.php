<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookNowController extends AbstractController
{
    #[Route('/book-now', name: 'app_book_now')]
    public function index(): Response
    {
        // ADMIN
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin');
        }

        // STAFF
        if ($this->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('app_staff');
        }

        // CUSTOMER
        if ($this->isGranted('ROLE_CUSTOMER')) {
            return $this->redirectToRoute('app_customer_booking_new');
        }

        // GUEST
        return $this->redirectToRoute('app_login');
    }
}
