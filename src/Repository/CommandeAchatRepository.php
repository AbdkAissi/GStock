<?php

namespace App\Repository;

use App\Entity\CommandeAchat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeAchat>
 */
class CommandeAchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeAchat::class);
    }

    /**
     * Retourne le nombre de commandes d'achat groupé par mois (YYYY-MM).
     *
     * @return array<int,array{mois:string,total:int}>
     */
    public function getNombreAchatParMois(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT DATE_FORMAT(cv.date_commande, '%Y-%m') AS mois, COUNT(cv.id) AS total
        FROM commande_achat cv
        GROUP BY mois
        ORDER BY mois
    ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        $achatParMois = $resultSet->fetchAllAssociative();

        // Formatage pour affichage plus clair dans le graphe (ex : "Mai 2025")
        foreach ($achatParMois as &$vente) {
            $date = \DateTime::createFromFormat('Y-m', $vente['mois']);
            $vente['mois'] = $date->format('M Y'); // ou 'F Y' pour le nom complet du mois
        }

        return $achatParMois;
    }
    // Dans CommandeAchatRepository
    public function getAchatsParMois(): array
    {
        return $this->createQueryBuilder('c')
            ->select('SUBSTRING(c.dateCommande, 1, 7) AS mois, COUNT(c.id) AS total')
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
