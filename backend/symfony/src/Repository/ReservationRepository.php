<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\DTO\ReservationDTO;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findReservationsByNameAndPeriod($name, $date1, $date2): array
    {
        return $this->createQueryBuilder('r')
        ->where('r.foodtruck = :name')
        ->andWhere('r.date BETWEEN :date1 AND :date2')
        ->setParameter('name', $name)
        ->setParameter('date1', $date1)
        ->setParameter('date2', $date2)
        ->getQuery()
        ->getResult();
    }


    public function findReservationsByDate($date): array
    {
        $date1 = (clone $date)->modify('00:00:00');
        $date2 = (clone $date)->modify('23:59:59');
        return $this->createQueryBuilder('r')
        ->where('r.date BETWEEN :date1 AND :date2')
        ->setParameter('date1', $date1)
        ->setParameter('date2', $date2)
        ->getQuery()
        ->getResult();
    }


}
