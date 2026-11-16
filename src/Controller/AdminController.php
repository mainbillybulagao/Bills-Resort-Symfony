<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Review;
use App\Entity\User;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    // ADMIN DASHBOARD
    #[Route('/admin', name: 'app_admin')]
    public function index(
        BookingRepository $bookingRepository,
        RoomRepository $roomRepository,
        UserRepository $userRepository,
        MessageRepository $messageRepository,
        ReviewRepository $reviewRepository
    ): Response {

        $totalBookings = $bookingRepository->count([]);
        $pendingBookings = $bookingRepository->count(['status' => 'Pending']);
        $totalRooms = $roomRepository->count([]);

        $users = $userRepository->findAll();
        $totalUsers = count($users);

        $totalStaff = 0;

        foreach ($users as $user) {
            $roles = $user->getRoles();

            if (
                in_array('ROLE_STAFF', $roles, true)
                && !in_array('ROLE_ADMIN', $roles, true)
            ) {
                $totalStaff++;
            }
        }

        $totalMessages = $messageRepository->count([]);
        $totalReviews = $reviewRepository->count([]);

        $totalRecords =
            $totalBookings
            + $totalRooms
            + $totalMessages
            + $totalReviews;

        return $this->render('admin/index.html.twig', [
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'totalRooms' => $totalRooms,
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalRecords' => $totalRecords,
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

    // EDIT ANY CUSTOMER BOOKING
    #[Route(
        '/admin/bookings/{id}/edit',
        name: 'app_admin_booking_edit',
        methods: ['GET', 'POST']
    )]
    public function editBooking(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $checkIn = $booking->getCheckIn();
            $checkOut = $booking->getCheckOut();

            if ($checkOut <= $checkIn) {
                $this->addFlash(
                    'error',
                    'Check-out date must be later than check-in date.'
                );

                return $this->render('admin/booking_edit.html.twig', [
                    'booking' => $booking,
                    'form' => $form,
                ]);
            }

            $nights = $checkIn->diff($checkOut)->days;

            $totalPrice = (float) $booking->getRoom()->getPrice() * $nights;

            $booking->setTotalPrice((string) $totalPrice);

            $entityManager->flush();

            $this->addFlash('success', 'Booking updated successfully.');

            return $this->redirectToRoute('app_admin_bookings');
        }

        return $this->render('admin/booking_edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    // DELETE ANY CUSTOMER BOOKING
    #[Route(
        '/admin/bookings/{id}/delete',
        name: 'app_admin_booking_delete',
        methods: ['POST']
    )]
    public function deleteBooking(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if (!$this->isCsrfTokenValid(
            'delete_booking_' . $booking->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($booking);
        $entityManager->flush();

        $this->addFlash('success', 'Booking deleted successfully.');

        return $this->redirectToRoute('app_admin_bookings');
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

            $this->addFlash('success', 'Booking confirmed successfully.');
        }

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

            $this->addFlash('success', 'Booking rejected successfully.');
        }

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
    public function messages(
        MessageRepository $messageRepository
    ): Response {

        return $this->render('admin/messages.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    // ADMIN REVIEW MANAGEMENT
    #[Route('/admin/reviews', name: 'app_admin_reviews')]
    public function reviews(
        ReviewRepository $reviewRepository
    ): Response {

        return $this->render('admin/reviews.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    // APPROVE CUSTOMER REVIEW
    #[Route(
        '/admin/reviews/{id}/approve',
        name: 'app_admin_review_approve',
        methods: ['POST']
    )]
    public function approveReview(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if (!$this->isCsrfTokenValid(
            'approve_review_' . $review->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($review->getStatus() === 'Pending') {
            $review->setStatus('Approved');
            $entityManager->flush();

            $this->addFlash('success', 'Review approved successfully.');
        }

        return $this->redirectToRoute('app_admin_reviews');
    }

    // REJECT CUSTOMER REVIEW
    #[Route(
        '/admin/reviews/{id}/reject',
        name: 'app_admin_review_reject',
        methods: ['POST']
    )]
    public function rejectReview(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if (!$this->isCsrfTokenValid(
            'reject_review_' . $review->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($review->getStatus() === 'Pending') {
            $review->setStatus('Rejected');
            $entityManager->flush();

            $this->addFlash('success', 'Review rejected successfully.');
        }

        return $this->redirectToRoute('app_admin_reviews');
    }

    // VIEW PUBLIC WEBSITE
    #[Route('/admin/website', name: 'app_admin_website')]
    public function website(): Response
    {
        return $this->render('admin/website.html.twig');
    }
}
