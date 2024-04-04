<?php

namespace App\Repository;

use App\Entity\Bill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bill::class);
    }

    // Returns bills before today.
    public function beforeToday(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.date < :today')
            ->orderBy('e.date', 'DESC')
            ->setParameter('today', new \DateTime('today'))
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();
    }
}