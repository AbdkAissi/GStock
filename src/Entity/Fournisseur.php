<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;


    /**
     * @var Collection<int, Paiement>
     */
    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'fournisseur')]
    private Collection $paiements;

    /**
     * @var Collection<int, CommandeAchat>
     */
    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: CommandeAchat::class)]
    private Collection $commandesAchat;

    public function __construct()
    {
        $this->paiements = new ArrayCollection();
        $this->commandesAchat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setFournisseur($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getFournisseur() === $this) {
                $paiement->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommandeAchat>
     */
    public function getCommandesAchat(): Collection
    {
        return $this->commandesAchat;
    }

    public function addCommandeAchat(CommandeAchat $commandeAchat): static
    {
        if (!$this->commandesAchat->contains($commandeAchat)) {
            $this->commandesAchat[] = $commandeAchat;
            $commandeAchat->setFournisseur($this);
        }

        return $this;
    }

    public function removeCommandeAchat(CommandeAchat $commandeAchat): static
    {
        if ($this->commandesAchat->removeElement($commandeAchat)) {
            if ($commandeAchat->getFournisseur() === $this) {
                $commandeAchat->setFournisseur(null);
            }
        }


        return $this;
    }
}
