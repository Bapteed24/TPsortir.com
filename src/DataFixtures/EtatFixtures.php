<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $Etets = [
            1 => 'En création',
            2 => 'Ouverte',
            3 => 'Clôturée',
            4 => 'En cours',
            5 => 'Terminée',
            6 => 'Annulée',
            7 => 'Historisée'
        ];

        foreach ($Etets as $keys => $e) {
//            dd($keys);
            $etat = new Etat();
            $etat->setId(intval($keys));
            $etat->setLibelle($e);
            $manager->persist($etat);
            $this->addReference('etat'.$keys, $etat);
        }

        $manager->flush();
    }
}
