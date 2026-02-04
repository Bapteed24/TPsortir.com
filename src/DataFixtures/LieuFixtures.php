<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // ligne a enlevé - temporaire
        $etat = new Etat();
        $etat->setLibelle('En cours');
        $manager->persist($etat);

        $ville = new Ville();
        $ville->setName('Paris');
        $ville->setCodePostal('75000');
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setName("Parc");
        $lieu->setStreet("Rue test");
        $lieu->setLatitude(48.866667);
        $lieu->setLongitute(2.333333);
        $lieu->setVille($ville);
        $manager->persist($lieu);
        $manager->flush();
    }
}
