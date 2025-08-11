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
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin', name: 'app_admin_')]
final class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $query = $userRepository->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/index.html.twig', [
            'users' => $pagination,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification d'unicité
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé par un autre compte.');
                return $this->render('admin/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            // Validation du mot de passe obligatoire pour la création
            $plainPassword = $form->get('plainPassword')->getData();
            if (empty($plainPassword)) {
                $this->addFlash('error', 'Le mot de passe est obligatoire lors de la création d\'un utilisateur.');
                return $this->render('admin/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            // Validation des rôles
            $roles = $user->getRoles();
            if (empty($roles) || (count($roles) === 1 && $roles[0] === 'ROLE_USER')) {
                // Si seulement ROLE_USER par défaut, vérifier qu'un rôle a été sélectionné
                $formRoles = $form->get('roles')->getData();
                if (empty($formRoles)) {
                    $this->addFlash('error', 'Veuillez sélectionner au moins un rôle pour l\'utilisateur.');
                    return $this->render('admin/new.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                    ]);
                }
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'app_admin_verify_email',
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

            try {
                $this->mailer->send($email);
                $this->addFlash('success', 'Utilisateur créé avec succès. Un email de confirmation a été envoyé.');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Utilisateur créé mais l\'email de confirmation n\'a pas pu être envoyé.');
            }

            return $this->redirectToRoute('app_admin_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('admin/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Sauvegarder l'email original pour comparaison
        $originalEmail = $user->getEmail();

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification d'unicité si email modifié
            $newEmail = $user->getEmail();
            if ($newEmail !== $originalEmail) {
                $existingUser = $userRepository->findOneBy(['email' => $newEmail]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Cet email est déjà utilisé par un autre utilisateur.');
                    return $this->render('admin/edit.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Validation des rôles
            $formRoles = $form->get('roles')->getData();
            if (empty($formRoles)) {
                $this->addFlash('error', 'Veuillez sélectionner au moins un rôle pour l\'utilisateur.');
                return $this->render('admin/edit.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            // Gestion du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->addFlash('info', 'Le mot de passe a été mis à jour.');
            }

            // Si l'email a changé, marquer comme non vérifié
            if ($newEmail !== $originalEmail) {
                $user->setIsVerified(false);
                $this->addFlash('info', 'L\'email a été modifié. L\'utilisateur devra le vérifier à nouveau.');
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_admin_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('admin/edit.html.twig', [
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
            return $this->redirectToRoute('app_admin_index');
        }

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà vérifié.');
            return $this->redirectToRoute('app_admin_index');
        }

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_admin_verify_email',
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
        return $this->redirectToRoute('app_admin_index');
    }

    #[Route('/verify/email', name: 'verify_email', methods: ['GET'])]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');

        if (!$id || !$user = $userRepository->find($id)) {
            return $this->redirectToRoute('app_admin_index');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_admin_index');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Ton adresse email a bien été confirmée.');
        return $this->redirectToRoute('app_admin_index');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_index');
    }


}
