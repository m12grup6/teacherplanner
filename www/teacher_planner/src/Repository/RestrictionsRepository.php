<?php

namespace App\Repository;

use App\Entity\Restrictions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Restrictions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restrictions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restrictions[]    findAll()
 * @method Restrictions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestrictionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restrictions::class);
    }

    // /**
    //  * @return Restrictions[] Returns an array of Restrictions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Restrictions
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
