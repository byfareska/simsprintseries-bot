<?php

namespace App\Repository;

use App\Entity\AssettoCorsaAssociatedName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssettoCorsaAssociatedName|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssettoCorsaAssociatedName|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssettoCorsaAssociatedName[]    findAll()
 * @method AssettoCorsaAssociatedName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssettoCorsaAssociatedNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssettoCorsaAssociatedName::class);
    }

    /**
     * @return AssettoCorsaAssociatedName[]
     */
    public function findAllByDiscordId(string $discord): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.discord = :val')
            ->setParameter('val', $discord)
            ->getQuery()
            ->getResult();
    }

    public function findOneByAssettoCorsaName(string $name): ?AssettoCorsaAssociatedName
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.assetto = :val')
            ->setParameter('val', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
