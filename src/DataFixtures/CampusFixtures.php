<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array
    {
        return ['users'];
    }

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

        $this->addReference('Campus1', $campus1);
        $this->addReference('Campus2', $campus2);
        $this->addReference('Campus3', $campus3);

        $manager->flush();
    }
}
