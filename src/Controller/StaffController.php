<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_STAFF')]
class StaffController extends AbstractController
{
    #[Route('/staff', name: 'app_staff')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Count bookings created by this staff member
        $totalBookings = $entityManager
            ->getRepository(Booking::class)
            ->count(['createdBy' => $user]);

        $pendingBookings = $entityManager
            ->getRepository(Booking::class)
            ->count([
                'createdBy' => $user,
                'status' => 'Pending',
            ]);

        $confirmedBookings = $entityManager
            ->getRepository(Booking::class)
            ->count([
                'createdBy' => $user,
                'status' => 'Confirmed',
            ]);

        // Count all messages
        $totalMessages = $entityManager
            ->getRepository(Message::class)
            ->count([]);

        return $this->render('staff/index.html.twig', [
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'totalMessages' => $totalMessages,
        ]);
    }
}
