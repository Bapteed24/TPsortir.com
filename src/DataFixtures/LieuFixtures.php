<?php

namespace App\DataFixtures;


use Faker\Factory;
use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['users'];
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $lieu1 = new Lieu();
        $lieu1->setName("Musée du Louvre");
        $lieu1->setStreet("Rue test");
        $lieu1->setLatitude(48.864824);
        $lieu1->setLongitute(2.334595);
        $ville1 = $this->getReference('ville1', Ville::Class);

        $lieu1->setVille($ville1);
        $manager->persist($lieu1);

        $lieu2 = new Lieu();
        $lieu2->setName("Disneyland Paris");
        $lieu2->setStreet("Route Nationale 34");
        $lieu2->setLatitude(48.87234);
        $lieu2->setLongitute(2.775808);
        $ville2 = $this->getReference('ville2', Ville::Class);
        $lieu2->setVille($ville2);
        $manager->persist($lieu2);

        $this->addReference('lieu1', $lieu1);
        $this->addReference('lieu2', $lieu2);

        $manager->flush();
    }
}
