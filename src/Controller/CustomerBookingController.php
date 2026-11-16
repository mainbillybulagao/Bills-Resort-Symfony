<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\User;
use App\Form\CustomerBookingType;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CUSTOMER')]
#[Route('/customer')]
final class CustomerBookingController extends AbstractController
{
    #[Route('/booking/new', name: 'app_customer_booking_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        RoomRepository $roomRepository
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        // Get all rooms from the database
        $rooms = $roomRepository->findBy([], ['id' => 'ASC']);

        $booking = new Booking();

        // Automatically get customer information
        $booking->setCustomerName($user->getName());
        $booking->setEmail($user->getEmail());

        $form = $this->createForm(CustomerBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Check booking dates
            if (
                !$booking->getCheckIn()
                || !$booking->getCheckOut()
                || $booking->getCheckOut() <= $booking->getCheckIn()
            ) {
                $this->addFlash(
                    'error',
                    'Check-out date must be later than check-in date.'
                );

                return $this->render('customer/booking/new.html.twig', [
                    'form' => $form,
                    'rooms' => $rooms,
                ]);
            }

            // Calculate number of nights
            $nights = $booking->getCheckIn()
                ->diff($booking->getCheckOut())
                ->days;

            // Calculate total price using selected room
            $roomPrice = (float) $booking->getRoom()->getPrice();
            $totalPrice = $roomPrice * $nights;

            // Automatically assign booking information
            $booking->setCustomerName($user->getName());
            $booking->setEmail($user->getEmail());
            $booking->setTotalPrice(number_format($totalPrice, 2, '.', ''));
            $booking->setStatus('Pending');
            $booking->setCreatedAt(new \DateTimeImmutable());
            $booking->setCreatedBy($user);

            $entityManager->persist($booking);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your booking has been submitted successfully!'
            );

            return $this->redirectToRoute('app_customer_bookings');
        }

        return $this->render('customer/booking/new.html.twig', [
            'form' => $form,
            'rooms' => $rooms,
        ]);
    }

    #[Route('/my-bookings', name: 'app_customer_bookings', methods: ['GET'])]
    public function myBookings(
        BookingRepository $bookingRepository
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        // Display only the logged-in customer's bookings
        $bookings = $bookingRepository->findBy(
            ['createdBy' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('customer/booking/index.html.twig', [
            'bookings' => $bookings,
        ]);
    }
}
