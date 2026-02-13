<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        /**
         * @return Sortie[] Returns an array of Sortie objects
         */
        public function listAccueil($user): array
        {

            $query = $this->createQueryBuilder('s');

            $query->where('s.etat != :etat or s.organisateurSortie = :organisateurSortie')
                  ->setParameter('etat', 1)
                  ->setParameter('organisateurSortie', $user);

            $now = new \DateTime();

            $now->modify('-30 day');

            $query->andWhere('s.dateHeureDebut > :now')
                    ->setParameter('now', $now);

            $query->addOrderBy('s.dateHeureDebut', 'ASC');

//            $query->andWhere('s.name LIKE :name')
//                   ->setParameter('name', '%2%');
            
            return $query->getQuery()->getResult();
//                ->getResult()
//            return $this->createQueryBuilder('s')
//                ->andWhere('s.exampleField = :val')
//                ->setParameter('val', $value)
//                ->orderBy('s.id', 'ASC')
//                ->setMaxResults(10)
//                ->getQuery()
//                ->getResult()
//            ;
        }
        public function apiList($parameters): array {
            $query = $this->createQueryBuilder('s');

            if (isset($parameters['etat']) &&
                !empty($parameters['etat']) &&
                $parameters['etat'] != 1 &&
                $parameters['etat'] != 5)
            {
                $query->where('s.etat = :etat')
                      ->setParameter('etat', $parameters['etat']);
            }
            else {
                $query->where('s.etat != :encours and s.etat != :termine')
                    ->setParameter('encours', 1)
                    ->setParameter('termine', 5);
            }


//            $query->getQuery()->getResult();
//
//            $paginator = new Paginator($query, true);

            return $query->getQuery()->getResult();

        }


}
