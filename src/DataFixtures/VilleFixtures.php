<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $ville1 = new Ville();
        $ville1->setName('Paris');
        $ville1->setCodePostal('75000');
        $manager->persist($ville1);

        $ville2 = new Ville();
        $ville2->setName('Chessy ');
        $ville2->setCodePostal('77700');
        $manager->persist($ville2);

        $this->addReference('ville1', $ville1);
        $this->addReference('ville2', $ville2);

        $manager->flush();
    }
}
