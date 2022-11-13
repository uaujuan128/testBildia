<?php

namespace App\Repository;

use App\Entity\Province;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Province>
 *
 * @method Province|null find($id, $lockMode = null, $lockVersion = null)
 * @method Province|null findOneBy(array $criteria, array $orderBy = null)
 * @method Province[]    findAll()
 * @method Province[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProvinceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Province::class);
    }

    public function getTotalPopulationFromProvinces(array $provincesIds): array
    {
        $result = $this->_em->createQueryBuilder()
            ->select('SUM(m.population) as total_population')
            ->from(Province::class, 'm')
            ->andWhere('m.id IN (:provinces)')
            ->setParameter('provinces', $provincesIds);
        ;

        return $result->getQuery()->getArrayResult();
    }

    public function getTotalPopulation(): array
    {

        $result = $this->_em->createQueryBuilder()
            ->select('SUM(m.population) as total_population')
            ->from(Province::class, 'm')
        ;

        return $result->getQuery()->getArrayResult();
    }

    public function getProvincesNames(array $provincesIds): array
    {

        $result = $this->_em->createQueryBuilder()
            ->select('m.name')
            ->from(Province::class, 'm')
            ->andWhere('m.id IN (:provinces)')
            ->setParameter('provinces', $provincesIds);
        ;

        return $result->getQuery()->getScalarResult();
    }
}
