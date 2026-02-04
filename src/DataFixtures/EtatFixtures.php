<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $Etets = [
            'En création',
            'Ouverte',
            'Clôturée',
            'En cours',
            'Terminée',
            'Annulée',
            'Historisée'
        ];

        foreach ($Etets as $e) {
            $etat = new Etat();
            $etat->setLibelle($e);
            $manager->persist($etat);
        }

        $manager->flush();
    }
}
