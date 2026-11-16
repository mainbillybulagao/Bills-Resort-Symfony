<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/review')]
final class ReviewController extends AbstractController
{
    // ADMIN: Redirect to Admin Reviews page
    #[Route(name: 'app_review_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->redirectToRoute('app_admin_reviews');
    }

    // CUSTOMER: Submit a review
    #[Route('/new', name: 'app_review_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $review->setCustomerName(
                $user->getName() ?? $user->getEmail() ?? 'Customer'
            );

            $review->setStatus('Pending');
            $review->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your review has been submitted successfully!'
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    // ADMIN: View a specific review
    #[Route('/{id}', name: 'app_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    // ADMIN: Edit a review
    #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Review $review,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Review updated successfully!');

            return $this->redirectToRoute('app_admin_reviews');
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    // ADMIN: Delete a review
    #[Route('/{id}', name: 'app_review_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Review $review,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid(
            'delete'.$review->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($review);
            $entityManager->flush();

            $this->addFlash('success', 'Review deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_reviews');
    }
}
