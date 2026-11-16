<?php

namespace App\Controller;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminReviewController extends AbstractController
{
    // DELETE CUSTOMER REVIEW
    #[Route(
        '/admin/reviews/{id}/delete',
        name: 'app_admin_review_delete',
        methods: ['POST']
    )]
    public function deleteReview(
        Review $review,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if (!$this->isCsrfTokenValid(
            'delete_review_' . $review->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($review);
        $entityManager->flush();

        $this->addFlash('success', 'Review deleted successfully.');

        return $this->redirectToRoute('app_admin_reviews');
    }
}