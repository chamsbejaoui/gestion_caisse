<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserCopieType;
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

#[Route('/user-copie', name: 'app_user_copie_')]
class UserCopieController extends AbstractController
{
    private $passwordHasher;
    private $verifyEmailHelper;
    private $mailer;
    private $userRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        VerifyEmailHelperInterface $verifyEmailHelper = null,
        MailerInterface $mailer = null,
        UserRepository $userRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Vérifie si un email existe déjà dans la base de données
     * 
     * @param string $email L'email à vérifier
     * @param int|null $excludeUserId ID de l'utilisateur à exclure de la vérification (pour l'édition)
     * @return bool True si l'email existe déjà, false sinon
     */
    private function isEmailAlreadyUsed(string $email, ?int $excludeUserId = null): bool
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        
        if (!$existingUser) {
            return false;
        }
        
        // Si on est en mode édition, on vérifie que l'utilisateur trouvé n'est pas celui qu'on édite
        if ($excludeUserId !== null && $existingUser->getId() === $excludeUserId) {
            return false;
        }
        
        return true;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $userRepository->createQueryBuilder('u')->orderBy('u.id', 'DESC'),
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('user_copie/index.html.twig', [
            'users' => $pagination,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserCopieType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification d'unicité de l'email
            if ($this->isEmailAlreadyUsed($user->getEmail())) {
                $this->addFlash('error', 'Erreur : Cette adresse email existe déjà dans le système.');
                return $this->render('user_copie/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            // Validation du mot de passe obligatoire pour la création
            $plainPassword = $form->get('plainPassword')->getData();
            if (empty($plainPassword)) {
                $this->addFlash('error', 'Le mot de passe est obligatoire lors de la création d\'un utilisateur.');
                return $this->render('user_copie/new.html.twig', [
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
                    return $this->render('user_copie/new.html.twig', [
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

            if ($this->verifyEmailHelper && $this->mailer) {
                $signatureComponents = $this->verifyEmailHelper->generateSignature(
                    'app_user_copie_verify_email',
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
            } else {
                $this->addFlash('success', 'Utilisateur créé avec succès.');
            }

            return $this->redirectToRoute('app_user_copie_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('user_copie/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user_copie/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $originalEmail = $user->getEmail();
        $form = $this->createForm(UserCopieType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = $user->getEmail();

            // Vérification d'unicité de l'email si modifié
            if ($newEmail !== $originalEmail && $this->isEmailAlreadyUsed($newEmail, $user->getId())) {
                $this->addFlash('error', 'Erreur : Cette adresse email existe déjà dans le système.');
                return $this->render('user_copie/edit.html.twig', [
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
            return $this->redirectToRoute('app_user_copie_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('user_copie/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_user_copie_index');
    }

    #[Route('/resend-verification', name: 'resend_verification', methods: ['GET'])]
    public function resendVerificationEmail(Request $request): Response
    {
        if (!$this->verifyEmailHelper || !$this->mailer) {
            $this->addFlash('error', 'La fonctionnalité de vérification d\'email n\'est pas disponible.');
            return $this->redirectToRoute('app_user_copie_index');
        }

        $id = $request->get('id');
        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_user_copie_index');
        }

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà vérifié.');
            return $this->redirectToRoute('app_user_copie_index');
        }

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_user_copie_verify_email',
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
        return $this->redirectToRoute('app_user_copie_index');
    }

    #[Route('/verify/email', name: 'verify_email', methods: ['GET'])]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->verifyEmailHelper) {
            return $this->redirectToRoute('app_user_copie_index');
        }

        $id = $request->get('id');

        if (!$id || !$user = $this->userRepository->find($id)) {
            return $this->redirectToRoute('app_user_copie_index');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_user_copie_index');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Ton adresse email a bien été confirmée.');
        return $this->redirectToRoute('app_user_copie_index');
    }
}