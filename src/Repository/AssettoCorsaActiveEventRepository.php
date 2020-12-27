<?php

namespace App\Repository;

use App\Entity\AssettoCorsaActiveEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssettoCorsaActiveEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssettoCorsaActiveEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssettoCorsaActiveEvent[]    findAll()
 * @method AssettoCorsaActiveEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssettoCorsaActiveEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssettoCorsaActiveEvent::class);
    }

    public function findOneById(int $id): ?AssettoCorsaActiveEvent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :val')
            ->setParameter('val', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
