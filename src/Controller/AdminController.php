<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    // ADMIN DASHBOARD
    #[Route('/admin', name: 'app_admin')]
    public function index(
        BookingRepository $bookingRepository,
        RoomRepository $roomRepository,
        UserRepository $userRepository
    ): Response {

        $totalBookings = $bookingRepository->count([]);

        $pendingBookings = $bookingRepository->count([
            'status' => 'Pending'
        ]);

        $totalRooms = $roomRepository->count([]);

        $totalUsers = $userRepository->count([]);

        return $this->render('admin/index.html.twig', [
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'totalRooms' => $totalRooms,
            'totalUsers' => $totalUsers,
        ]);
    }

    // ADMIN BOOKING MANAGEMENT
    #[Route('/admin/bookings', name: 'app_admin_bookings')]
    public function bookings(
        BookingRepository $bookingRepository
    ): Response {

        return $this->render('admin/bookings.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    // CONFIRM CUSTOMER BOOKING
    #[Route(
        '/admin/bookings/{id}/confirm',
        name: 'app_admin_booking_confirm',
        methods: ['POST']
    )]
    public function confirmBooking(
        int $id,
        Request $request,
        BookingRepository $bookingRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $booking = $bookingRepository->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Booking not found.');
        }

        if (!$this->isCsrfTokenValid(
            'confirm_booking_' . $booking->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($booking->getStatus() === 'Pending') {
            $booking->setStatus('Confirmed');
            $entityManager->flush();
        }

        $this->addFlash('success', 'Booking confirmed successfully.');

        return $this->redirectToRoute('app_admin_bookings');
    }

    // REJECT CUSTOMER BOOKING
    #[Route(
        '/admin/bookings/{id}/reject',
        name: 'app_admin_booking_reject',
        methods: ['POST']
    )]
    public function rejectBooking(
        int $id,
        Request $request,
        BookingRepository $bookingRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $booking = $bookingRepository->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Booking not found.');
        }

        if (!$this->isCsrfTokenValid(
            'reject_booking_' . $booking->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($booking->getStatus() === 'Pending') {
            $booking->setStatus('Rejected');
            $entityManager->flush();
        }

        $this->addFlash('success', 'Booking rejected successfully.');

        return $this->redirectToRoute('app_admin_bookings');
    }

    // ADMIN ROOM MANAGEMENT
    #[Route('/admin/rooms', name: 'app_admin_rooms')]
    public function rooms(): Response
    {
        return $this->render('admin/rooms.html.twig');
    }

    // ADMIN MESSAGE MANAGEMENT
    #[Route('/admin/messages', name: 'app_admin_messages')]
    public function messages(): Response
    {
        return $this->render('admin/messages.html.twig');
    }

    // ADMIN REVIEW MANAGEMENT
    #[Route('/admin/reviews', name: 'app_admin_reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/reviews.html.twig');
    }

    // VIEW PUBLIC WEBSITE
    #[Route('/admin/website', name: 'app_admin_website')]
    public function website(): Response
    {
        return $this->render('admin/website.html.twig');
    }
}
