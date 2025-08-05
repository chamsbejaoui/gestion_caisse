<?php

namespace App\Service;

use App\Entity\Caisse;
use App\Repository\CaisseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CaisseService
{
    private Caisse $caisse;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CaisseRepository $caisseRepository,
        private readonly UserRepository $userRepository,
        private readonly MailerInterface $mailer
    ) {
        // On s'assure qu'il y a toujours une seule instance de Caisse
        $caisse = $this->caisseRepository->findOneBy([]);
        if (!$caisse) {
            $caisse = new Caisse();
            $this->em->persist($caisse);
            $this->em->flush();
        }
        $this->caisse = $caisse;
    }

    public function getCaisse(): Caisse
    {
        return $this->caisse;
    }

    public function updateSolde(float $montant): void
    {
        $soldeActuel = $this->caisse->getSolde();
        $this->caisse->setSolde($soldeActuel + $montant);
        $this->em->flush();

        $this->checkSeuilAndNotify();
    }

    public function checkSeuilAndNotify(): void
    {
        $solde = $this->caisse->getSolde();
        $seuil = $this->caisse->getSeuilAlerte();

        // Si le solde est sous le seuil et qu'aucune notification n'a été envoyée
        if ($solde < $seuil && !$this->caisse->isNotificationEnvoyee()) {
            $this->sendAlertEmail();
            $this->caisse->setNotificationEnvoyee(true);
            $this->em->flush();
        }

        // Si le solde remonte au-dessus du seuil, on réinitialise le statut de notification
        if ($solde >= $seuil && $this->caisse->isNotificationEnvoyee()) {
            $this->caisse->setNotificationEnvoyee(false);
            $this->em->flush();
        }
    }

    private function sendAlertEmail(): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $adminEmails = array_map(fn($admin) => $admin->getEmail(), $admins);

        if (empty($adminEmails)) {
            return; // Pas d'admin à notifier
        }

        $email = (new Email())
            ->from('no-reply@votre-caisse.com')
            ->to(...$adminEmails)
            ->subject('Alerte : Solde de la caisse bas')
            ->html($this->getEmailTemplate());

        $this->mailer->send($email);
    }

    private function getEmailTemplate(): string
    {
        $solde = $this->caisse->getSolde();
        $seuil = $this->caisse->getSeuilAlerte();

        return <<<HTML
            <h1>Alerte de solde bas</h1>
            <p>Bonjour,</p>
            <p>Le solde de la caisse est passé en dessous du seuil d'alerte.</p>
            <ul>
                <li><strong>Solde actuel :</strong> {$solde} TND</li>
                <li><strong>Seuil d'alerte :</strong> {$seuil} TND</li>
            </ul>
            <p>Veuillez prendre les mesures nécessaires.</p>
            <p>L'équipe de gestion</p>
        HTML;
    }
}
