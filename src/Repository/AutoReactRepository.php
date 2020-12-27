<?php

namespace App\Repository;

use App\Entity\AutoReact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoReact|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoReact|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoReact[]    findAll()
 * @method AutoReact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoReactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutoReact::class);
    }

    // /**
    //  * @return AutoReact[] Returns an array of AutoReact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AutoReact
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
