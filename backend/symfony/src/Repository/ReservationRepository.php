<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByNameAndPeriod($name, $date1, $date2): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT r
            FROM App\Entity\Reservation r
            WHERE ( r.date BETWEEN :date1 AND :date2 ) AND ( r.foodtruck = :name )'
        )
        ->setParameter('name', $name)
        ->setParameter('date1', $date1)
        ->setParameter('date2', $date2);
        return $query->getResult();
    }


    public function countByDate($date): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT COUNT(r.id)
            FROM App\Entity\Reservation r
            WHERE r.date BETWEEN :date1 AND :date2'
        )
        ->setParameter('date1', $date->format('Y-m-d 00:00:00'))
        ->setParameter('date2', $date->format('Y-m-d 23:59:59'));
        return $query->getResult();
    }


}
