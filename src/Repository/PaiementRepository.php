<?php

namespace App\Repository;

use App\Entity\Paiement;
use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 *
 * @method Paiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Paiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Paiement[]    findAll()
 * @method Paiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    /**
     * Récupère tous les paiements associés à une commande d'achat donnée.
     */
    public function findByCommandeAchat(CommandeAchat $commande): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.commandeAchat = :commande')
            ->setParameter('commande', $commande)
            ->orderBy('p.date', 'ASC') // correction ici
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les paiements associés à une commande de vente donnée.
     */
    public function findByCommandeVente(CommandeVente $commande): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.commandeVente = :commande')
            ->setParameter('commande', $commande)
            ->orderBy('p.date', 'ASC') // correction ici
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des paiements pour une commande d'achat.
     */
    public function getTotalPayéAchat(CommandeAchat $commande): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(p.montant)')
            ->andWhere('p.commandeAchat = :commande')
            ->setParameter('commande', $commande)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule le total des paiements pour une commande de vente.
     */
    public function getTotalPayéVente(CommandeVente $commande): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(p.montant)')
            ->andWhere('p.commandeVente = :commande')
            ->setParameter('commande', $commande)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les paiements filtrés par type (achat ou vente).
     * Utile pour des stats globales ou des graphiques.
     */
    public function findByType(string $type): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($type === 'achat') {
            $qb->andWhere('p.commandeAchat IS NOT NULL');
        } elseif ($type === 'vente') {
            $qb->andWhere('p.commandeVente IS NOT NULL');
        }

        return $qb->orderBy('p.date', 'DESC') // correction ici
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les montants totaux par état et type (vente/achat)
     */
    public function getMontantsParEtatEtType(): array
    {
        // Paiements de vente
        $qbVente = $this->createQueryBuilder('p')
            ->select('p.etatPaiement, SUM(p.montant) as total')
            ->where('p.commandeVente IS NOT NULL')
            ->groupBy('p.etatPaiement');

        $resultVente = $qbVente->getQuery()->getResult();

        // Paiements d'achat
        $qbAchat = $this->createQueryBuilder('p')
            ->select('p.etatPaiement, SUM(p.montant) as total')
            ->where('p.commandeAchat IS NOT NULL')
            ->groupBy('p.etatPaiement');

        $resultAchat = $qbAchat->getQuery()->getResult();

        // Formatage du résultat
        $formattedResult = [];

        foreach ($resultVente as $item) {
            $formattedResult['Vente - ' . $item['etatPaiement']] = (float) $item['total'];
        }

        foreach ($resultAchat as $item) {
            $formattedResult['Achat - ' . $item['etatPaiement']] = (float) $item['total'];
        }

        return $formattedResult;
    }

    /**
     * Récupère les montants par état et type pour une année donnée
     */
    public function getMontantsParEtatEtTypeParAnnee(int $annee): array
    {
        $dateDebut = new \DateTime($annee . '-01-01');
        $dateFin = new \DateTime($annee . '-12-31 23:59:59');

        // Paiements de vente
        $qbVente = $this->createQueryBuilder('p')
            ->select('p.etatPaiement, SUM(p.montant) as total')
            ->where('p.commandeVente IS NOT NULL')
            ->andWhere('p.date >= :dateDebut') // correction ici
            ->andWhere('p.date <= :dateFin')   // correction ici
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->groupBy('p.etatPaiement');

        $resultVente = $qbVente->getQuery()->getResult();

        // Paiements d'achat
        $qbAchat = $this->createQueryBuilder('p')
            ->select('p.etatPaiement, SUM(p.montant) as total')
            ->where('p.commandeAchat IS NOT NULL')
            ->andWhere('p.date >= :dateDebut') // correction ici
            ->andWhere('p.date <= :dateFin')   // correction ici
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->groupBy('p.etatPaiement');

        $resultAchat = $qbAchat->getQuery()->getResult();

        // Formatage du résultat
        $formattedResult = [];

        foreach ($resultVente as $item) {
            $formattedResult['Vente - ' . $item['etatPaiement']] = (float) $item['total'];
        }

        foreach ($resultAchat as $item) {
            $formattedResult['Achat - ' . $item['etatPaiement']] = (float) $item['total'];
        }

        return $formattedResult;
    }

    /**
     * Récupère les montants totaux par état de paiement (ancienne méthode pour compatibilité)
     */
    public function getMontantsParEtat(): array
    {
        return $this->getMontantsParEtatEtType();
    }

    /**
     * Récupère les montants par état pour une année donnée (ancienne méthode pour compatibilité)
     */
    public function getMontantsParEtatParAnnee(int $annee): array
    {
        return $this->getMontantsParEtatEtTypeParAnnee($annee);
    }

    /**
     * Récupère toutes les années disponibles dans les paiements (version sécurisée)
     */
    public function getAnneesPaiements(): array
    {
        // Méthode sûre utilisant uniquement Doctrine ORM
        $qb = $this->createQueryBuilder('p')
            ->select('p.date')
            ->where('p.date IS NOT NULL')
            ->orderBy('p.date', 'DESC'); // correction ici

        $result = $qb->getQuery()->getResult();

        // Extraction des années en PHP
        $annees = [];
        foreach ($result as $item) {
            if ($item['date'] instanceof \DateTime) {
                $annee = (int) $item['date']->format('Y');
                if (!in_array($annee, $annees)) {
                    $annees[] = $annee;
                }
            }
        }

        // Tri décroissant et suppression des doublons
        rsort($annees);
        return array_values(array_unique($annees));
    }
}
