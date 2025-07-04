<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserPasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:hash-existing-passwords',
    description: 'Hache les mots de passe existants stockés en clair dans la base de données',
)]
class HashExistingPasswordsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasher $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasher $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Récupérer tous les utilisateurs
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        
        $count = 0;
        
        foreach ($users as $user) {
            $plainPassword = $user->getPassword(); // Récupérer le mot de passe en clair
            
            if ($plainPassword) {
                // Hacher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $count++;
            }
        }
        
        // Persister les modifications
        $this->entityManager->flush();
        
        $io->success(sprintf('Les mots de passe de %d utilisateurs ont été hashés avec succès', $count));
        
        return Command::SUCCESS;
    }
}