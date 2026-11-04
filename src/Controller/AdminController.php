<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/bookings', name: 'app_admin_bookings')]
    public function bookings(): Response
    {
        return $this->render('admin/bookings.html.twig');
    }

    #[Route('/admin/rooms', name: 'app_admin_rooms')]
    public function rooms(): Response
    {
        return $this->render('admin/rooms.html.twig');
    }

    #[Route('/admin/messages', name: 'app_admin_messages')]
    public function messages(): Response
    {
        return $this->render('admin/messages.html.twig');
    }

    #[Route('/admin/reviews', name: 'app_admin_reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/reviews.html.twig');
    }

    #[Route('/admin/website', name: 'app_admin_website')]
    public function website(): Response
    {
        return $this->render('admin/website.html.twig');
    }
}