<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    // DISPLAY ALL USERS
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    // CREATE NEW USER
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $user->setStatus('Active');
        $user->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User account created successfully.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    // DISPLAY USER DETAILS
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    // EDIT USER
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );

                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User account updated successfully.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    // DELETE OR DEACTIVATE USER
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {

        // Prevent Admin from deleting their own account
$currentUser = $this->getUser();

if ($currentUser instanceof User && $user->getId() === $currentUser->getId()) {
    $this->addFlash('error', 'You cannot delete your own account.');

    return $this->redirectToRoute('app_user_index');
}

        // Validate CSRF token
        if (!$this->isCsrfTokenValid(
            'delete' . $user->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Check if the user has booking history
        if (!$user->getBookings()->isEmpty()) {

            // Deactivate account instead of deleting
            $user->setStatus('Inactive');

            $entityManager->flush();

            $this->addFlash(
                'success',
                'User has booking history. Account was deactivated instead of deleted.'
            );

        } else {

            // Permanently delete if no bookings exist
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'User account deleted successfully.'
            );
        }

        return $this->redirectToRoute('app_user_index');
    }
}
