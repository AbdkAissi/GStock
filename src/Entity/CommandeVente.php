<?php

namespace App\Entity;

use App\Repository\CommandeVenteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\LigneCommandeVente;
use App\Entity\Client;

#[ORM\Entity(repositoryClass: CommandeVenteRepository::class)]
class CommandeVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = 'en_attente';


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'commandeVente', targetEntity: LigneCommandeVente::class, cascade: ['persist', 'remove'], orphanRemoval: true)]    private Collection $lignesCommandeVente;

    public function __construct()
    {
        $this->lignesCommandeVente = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setLignesCommandeVente(Collection $lignes): static
    {
        // Supprimer les lignes qui ne sont plus présentes
        foreach ($this->lignesCommandeVente as $existingLigne) {
            if (!$lignes->contains($existingLigne)) {
                $this->removeLigneCommandeVente($existingLigne);
            }
        }

        // Ajouter les nouvelles lignes
        foreach ($lignes as $ligne) {
            if (!$this->lignesCommandeVente->contains($ligne)) {
                $this->addLigneCommandeVente($ligne);
            }
        }

        return $this;
    }

    // src/Entity/CommandeVente.php

    public function getLignesCommandeAffichage(): string
    {
        if ($this->getLignesCommandeVente()->isEmpty()) {
            return '<div class="text-muted">Aucune ligne</div>';
        }

        $id = 'accordion-lignes-' . $this->getId();
        $html = <<<HTML
<div class="accordion" id="{$id}">
  <div class="accordion-item bg-light text-dark">
    <h2 class="accordion-header" id="heading{$id}">
      <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{$id}" aria-expanded="false" aria-controls="collapse{$id}">
        Voir les lignes de commande
      </button>
    </h2>
    <div id="collapse{$id}" class="accordion-collapse collapse" aria-labelledby="heading{$id}" data-bs-parent="#{$id}">
      <div class="accordion-body p-2">
        <ul class="mb-0 ps-3">
HTML;

        foreach ($this->getLignesCommandeVente() as $ligne) {
            $produit = htmlspecialchars($ligne->getProduit()->getNom());
            $quantite = $ligne->getQuantite();
            $prixUnitaire = number_format($ligne->getPrixUnitaire(), 2, ',', ' ');
            $total = number_format($ligne->getQuantite() * $ligne->getPrixUnitaire(), 2, ',', ' ');
            $html .= "<li><strong>{$produit}</strong> × {$quantite} à {$prixUnitaire} MAD (Total : {$total} MAD)</li>";
        }

        $html .= '</ul></div></div></div></div>';

        return $html;
    }

    public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeImmutable $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getLignesCommandeVente(): Collection
    {
        return $this->lignesCommandeVente;
    }

    public function addLigneCommandeVente(LigneCommandeVente $ligne): static
    {
        if (!$this->lignesCommandeVente->contains($ligne)) {
            $this->lignesCommandeVente[] = $ligne;
            $ligne->setCommandeVente($this);
        }
        return $this;
    }

    public function removeLigneCommandeVente(LigneCommandeVente $ligne): static
    {
        if ($this->lignesCommandeVente->removeElement($ligne)) {
            if ($ligne->getCommandeVente() === $this) {
                $ligne->setCommandeVente(null);
            }
        }
        return $this;
    }
    public function getTotalCommande(): float
    {
        $total = 0;
        foreach ($this->lignesCommandeVente as $ligne) {
            $prixUnitaire = $ligne->getPrixUnitaire(); // ne doit jamais être null, ou 0 par défaut
            $quantite = $ligne->getQuantite(); // ne doit jamais être null, ou 0 par défaut
            if ($prixUnitaire > 0 && $quantite > 0) {
                $total += $prixUnitaire * $quantite;
            }
        }
        return $total;
    }
}
