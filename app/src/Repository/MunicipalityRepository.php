<?php

namespace App\Repository;

use App\Entity\Municipality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Municipality>
 *
 * @method Municipality|null find($id, $lockMode = null, $lockVersion = null)
 * @method Municipality|null findOneBy(array $criteria, array $orderBy = null)
 * @method Municipality[]    findAll()
 * @method Municipality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MunicipalityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Municipality::class);
    }

    public function getNearestByCardinal($municipalityIds, $cardinal): array
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('m')
            ->from(Municipality::class, 'm');

        if ($cardinal == 'e' || $cardinal == 'w') {
            $queryBuilder->andWhere(
                $this->_em->getExpressionBuilder()->in(
                    ('m.latitude'),
                    $this->_em->createQueryBuilder()->select(($cardinal == 'e') ? 'MAX(m2.latitude)' : 'MIN(m2.latitude)')->from(Municipality::class, 'm2')
                        ->andWhere('m2.id IN (:municipality)')
                        ->getDQL()
                )
            );
        } else {
            $queryBuilder->andWhere(
                $this->_em->getExpressionBuilder()->in(
                    ('m.longitude'),
                    $this->_em->createQueryBuilder()->select(($cardinal == 'n') ? 'MAX(m2.longitude)' : 'MIN(m2.longitude)')->from(Municipality::class, 'm2')
                        ->andWhere('m2.id IN (:municipality)')
                        ->getDQL()
                )
            );
        }

        $queryBuilder->setParameter('municipality', $municipalityIds);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getMunicipalities(): Array
    {
        $queryBuilder =  $this->_em->createQueryBuilder()
            ->select('m.id, m.slug, m.name, m.latitude, m.longitude, p.id as idProvince, p.name as nameProvince')
            ->from(Municipality::class, 'm')
            ->join('m.province', 'p')
        ;

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getPaginatedMunicipalities(array $pagination): Paginator
    {
        $queryBuilder =  $this->_em->createQueryBuilder()
            ->select('m')
            ->from(Municipality::class, 'm')
            ->join('m.province', 'p')
            ->orderBy('m.id',  'DESC')
        ;

        $paginatedQuery = $queryBuilder->getQuery()
            ->setFirstResult($pagination['maxResults'] * ($pagination['page'] - 1))
            ->setMaxResults($pagination['maxResults'])
        ;

        return new Paginator($paginatedQuery);
    }
}
