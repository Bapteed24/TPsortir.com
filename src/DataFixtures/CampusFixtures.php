<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campus1 = new Campus();
        $campus1->setName("Campus 1");
        $manager->persist($campus1);

        $campus2 = new Campus();
        $campus2->setName("Campus 2");
        $manager->persist($campus2);

        $campus3 = new Campus();
        $campus3->setName("Campus 3");
        $manager->persist($campus3);

        $manager->flush();
    }
}
