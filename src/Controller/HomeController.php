<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if ($request->isMethod('POST')) {

            // Check CSRF token
            if (!$this->isCsrfTokenValid(
                'contact_message',
                $request->request->get('_token')
            )) {
                $this->addFlash('error', 'Invalid form submission.');

                return $this->redirectToRoute('app_home', [
                    '_fragment' => 'contact'
                ]);
            }

            // Get form values
            $name = trim($request->request->getString('name'));
            $email = trim($request->request->getString('email'));
            $subject = trim($request->request->getString('subject'));
            $messageText = trim($request->request->getString('message'));

            // Validate required fields
            if (
                $name === '' ||
                $email === '' ||
                $subject === '' ||
                $messageText === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($name) > 100 ||
                strlen($email) > 150 ||
                strlen($subject) > 150
            ) {
                $this->addFlash('error', 'Please check your information and try again.');
            } else {

                // Create and save message
                $message = new Message();

                $message->setName($name);
                $message->setEmail($email);
                $message->setSubject($subject);
                $message->setMessage($messageText);
                $message->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Your message has been sent successfully!');
            }

            return $this->redirectToRoute('app_home', [
                '_fragment' => 'contact'
            ]);
        }

        return $this->render('home/index.html.twig');
    }
}
