<?php

namespace App\Entity;

use App\Repository\CaisseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]
class Caisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $solde = 0;

    #[ORM\Column]
    private ?float $seuilAlerte = 0;

    #[ORM\Column]
    private ?bool $notificationEnvoyee = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(float $solde): static
    {
        $this->solde = $solde;

        return $this;
    }

    public function getSeuilAlerte(): ?float
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(float $seuilAlerte): static
    {
        $this->seuilAlerte = $seuilAlerte;

        return $this;
    }

    public function isNotificationEnvoyee(): ?bool
    {
        return $this->notificationEnvoyee;
    }

    public function setNotificationEnvoyee(bool $notificationEnvoyee): static
    {
        $this->notificationEnvoyee = $notificationEnvoyee;

        return $this;
    }
}
