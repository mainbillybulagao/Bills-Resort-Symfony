<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
final class AdminProfileController extends AbstractController
{
    // VIEW ADMIN PROFILE
    #[Route('/admin/profile', name: 'app_admin_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin_profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    // UPDATE ADMIN PROFILE
    #[Route('/admin/profile/update', name: 'app_admin_profile_update', methods: ['POST'])]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'update_admin_profile',
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $name = trim($request->request->get('name', ''));
        $email = trim($request->request->get('email', ''));

        if (
            $name === ''
            || strlen($name) > 100
            || $email === ''
            || strlen($email) > 180
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $this->addFlash('error', 'Please enter a valid name and email address.');

            return $this->redirectToRoute('app_admin_profile');
        }

        $existingUser = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
            $this->addFlash('error', 'This email address is already in use.');

            return $this->redirectToRoute('app_admin_profile');
        }

        $user->setName($name);
        $user->setEmail($email);

        $entityManager->flush();

        $this->addFlash('success', 'Your profile was updated successfully.');

        return $this->redirectToRoute('app_admin_profile');
    }

    // CHANGE ADMIN PASSWORD
    #[Route('/admin/profile/change-password', name: 'app_admin_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'change_admin_password',
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $currentPassword = $request->request->get('current_password', '');
        $newPassword = $request->request->get('new_password', '');
        $confirmPassword = $request->request->get('confirm_password', '');

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Your current password is incorrect.');

            return $this->redirectToRoute('app_admin_profile');
        }

        if (strlen($newPassword) < 8) {
            $this->addFlash('error', 'Your new password must have at least 8 characters.');

            return $this->redirectToRoute('app_admin_profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'The new passwords do not match.');

            return $this->redirectToRoute('app_admin_profile');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashedPassword);

        $entityManager->flush();

        $this->addFlash('success', 'Your password was changed successfully.');

        return $this->redirectToRoute('app_admin_profile');
    }
}
