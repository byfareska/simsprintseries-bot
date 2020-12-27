<?php

namespace App\Repository;

use App\Entity\AssettoCorsaActiveEvent;
use App\Entity\AssettoCorsaGaveRank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssettoCorsaGaveRank|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssettoCorsaGaveRank|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssettoCorsaGaveRank[]    findAll()
 * @method AssettoCorsaGaveRank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssettoCorsaGaveRankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssettoCorsaGaveRank::class);
    }

    public function findByInstance(AssettoCorsaActiveEvent $instance): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.instance = :val')
            ->setParameter('val', $instance)
            ->getQuery()
            ->getResult();
    }

    public function deleteByInstance(AssettoCorsaActiveEvent $instance): int
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.instance = :val')
            ->setParameter('val', $instance)
            ->delete()
            ->getQuery()
            ->execute();
    }

    /*
    public function findOneBySomeField($value): ?AssettoCorsaGaveRank
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
