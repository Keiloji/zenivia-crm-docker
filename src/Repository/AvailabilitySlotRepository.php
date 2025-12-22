<?php

namespace App\Repository;

use App\Entity\AvailabilitySlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AvailabilitySlot>
 */
class AvailabilitySlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvailabilitySlot::class);
    }
    /**
     * Récupère tous les créneaux disponibles (non réservés et dans le futur).
     * @return AvailabilitySlot[]
     */
    public function findAvailableSlots(): array
    {
        return $this->createQueryBuilder('a')
            // Critère 1: Le créneau n'est pas réservé
            ->andWhere('a.isBooked = :isBooked')
            ->setParameter('isBooked', false)

            // Critère 2: Le créneau est dans le futur
            ->andWhere('a.startTime > :now')
            ->setParameter('now', new \DateTimeImmutable())

            // Trier par date de début
            ->orderBy('a.startTime', 'ASC')

            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return AvailabilitySlot[] Returns an array of AvailabilitySlot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AvailabilitySlot
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
