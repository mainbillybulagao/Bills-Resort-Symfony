<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\User;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/booking')]
final class BookingController extends AbstractController
{
    // VIEW BOOKINGS
    #[Route(name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $bookings = $bookingRepository->findAll();
        } else {
            $currentUser = $this->getUser();

            if (!$currentUser instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $bookings = $bookingRepository->findBy([
                'createdBy' => $currentUser,
            ]);
        }

        return $this->render('booking/index.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    // CREATE BOOKING
    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $booking = new Booking();

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // CHECK BOOKING DATES
            if (
                $booking->getCheckIn() !== null
                && $booking->getCheckOut() !== null
                && $booking->getCheckOut() <= $booking->getCheckIn()
            ) {
                $form->get('checkOut')->addError(
                    new FormError('Check-out date must be later than check-in date.')
                );
            }

            if ($form->isValid()) {

                $booking->setCreatedBy($currentUser);

                $entityManager->persist($booking);
                $entityManager->flush();

                $this->addFlash('success', 'Booking created successfully.');

                return $this->redirectToRoute(
                    'app_booking_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    // VIEW BOOKING DETAILS
    #[Route('/{id}', name: 'app_booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        $this->denyIfNotOwner($booking);

        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    // EDIT BOOKING
    #[Route('/{id}/edit', name: 'app_booking_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Booking $booking,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyIfNotOwner($booking);

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // CHECK BOOKING DATES
            if (
                $booking->getCheckIn() !== null
                && $booking->getCheckOut() !== null
                && $booking->getCheckOut() <= $booking->getCheckIn()
            ) {
                $form->get('checkOut')->addError(
                    new FormError('Check-out date must be later than check-in date.')
                );
            }

            if ($form->isValid()) {

                $entityManager->flush();

                $this->addFlash('success', 'Booking updated successfully.');

                return $this->redirectToRoute(
                    'app_booking_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    // DELETE BOOKING
    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Booking $booking,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyIfNotOwner($booking);

        if (!$this->isCsrfTokenValid(
            'delete' . $booking->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($booking);
        $entityManager->flush();

        $this->addFlash('success', 'Booking deleted successfully.');

        return $this->redirectToRoute(
            'app_booking_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    // CHECK BOOKING OWNERSHIP
    private function denyIfNotOwner(Booking $booking): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $currentUser = $this->getUser();

        if (
            !$currentUser instanceof User
            || $booking->getCreatedBy()?->getId() !== $currentUser->getId()
        ) {
            throw $this->createAccessDeniedException(
                'You are not allowed to manage this booking.'
            );
        }
    }
}
