<?php

namespace App\Repository;

use App\Entity\CommandeVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeVente>
 */
class CommandeVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeVente::class);
    }

    //    /**
    //     * @return CommandeVente[] Returns an array of CommandeVente objects
    //     */

    public function getNombreVentesParMois(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT DATE_FORMAT(cv.date_commande, '%Y-%m') AS mois, COUNT(cv.id) AS total
        FROM commande_vente cv
        GROUP BY mois
        ORDER BY mois
    ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        $ventesParMois = $resultSet->fetchAllAssociative();

        // Formatage pour affichage plus clair dans le graphe (ex : "Mai 2025")
        foreach ($ventesParMois as &$vente) {
            $date = \DateTime::createFromFormat('Y-m', $vente['mois']);
            $vente['mois'] = $date->format('M Y'); // ou 'F Y' pour le nom complet du mois
        }

        return $ventesParMois;
    }
    // Dans CommandeVenteRepository
    public function getVentesParMois(): array
    {
        return $this->createQueryBuilder('c')
            ->select('SUBSTRING(c.dateCommande, 1, 7) AS mois, COUNT(c.id) AS total')
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
