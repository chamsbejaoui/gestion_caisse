<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/user', name: 'app_user_')]
final class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // Génération du lien de confirmation
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'app_user_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = (new Email())
                ->from('chams2002bejaoui@gmail.com')
                ->to($user->getEmail())
                ->subject('Confirmation de votre compte')
                ->html($this->renderView('emails/confirmation.html.twig', [
                    'signedUrl' => $signatureComponents->getSignedUrl()
                ]));

            $this->mailer->send($email);

            $this->addFlash('success', 'Un email de confirmation a été envoyé.');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/resend-verification', name: 'resend_verification', methods: ['GET'])]
    public function resendVerificationEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà vérifié.');
            return $this->redirectToRoute('app_user_index');
        }

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_user_verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new Email())
            ->from('chams2002bejaoui@gmail.com')
            ->to($user->getEmail())
            ->subject('Confirmation de votre compte')
            ->html($this->renderView('emails/confirmation.html.twig', [
                'signedUrl' => $signatureComponents->getSignedUrl()
            ]));

        $this->mailer->send($email);

        $this->addFlash('success', 'Un nouvel email de confirmation a été envoyé.');
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/verify/email', name: 'verify_email', methods: ['GET'])]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');

        if (!$id || !$user = $userRepository->find($id)) {
            return $this->redirectToRoute('app_user_index');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_user_index');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Ton adresse email a bien été confirmée.');
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index');
    }
}
