<?php
// src/Doctrine/Extension/SortieExcludeEtatsExtension.php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sortie;
use Doctrine\ORM\QueryBuilder;

final class SortieExcludeEtatsExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $qb,
        QueryNameGeneratorInterface $qng,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Sortie::class) {
            return;
        }

        $alias = $qb->getRootAliases()[0];

        // ✅ Exclure 2 états (adapte les valeurs)
        $qb
            ->andWhere(sprintf('%s.etat NOT IN (:excluded)', $alias))
//            ->setParameter('excluded', ['CREATION', 'TERMINEE']);
            ->setParameter('excluded', [1, 7]);
//            ->setParameter('excluded', [EtatSortie::CREATION, EtatSortie::TERMINEE]);
    }
}