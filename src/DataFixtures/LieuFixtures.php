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

        $ville = new Ville();
        $ville->setName('Paris');
        $ville->setCodePostal('75000');
        $manager->persist($ville);

        $lieu = new Lieu();
        $lieu->setName("Musée du Louvre");
        $lieu->setStreet("Rue test");
        $lieu->setLatitude(48.864824);
        $lieu->setLongitute(2.334595);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $ville2 = new Ville();
        $ville2->setName('Chessy ');
        $ville2->setCodePostal('77700');
        $manager->persist($ville2);

        $lieu2 = new Lieu();
        $lieu2->setName("Disneyland Paris");
        $lieu2->setStreet("Route Nationale 34");
        $lieu2->setLatitude(48.87234);
        $lieu2->setLongitute(2.775808);
        $lieu2->setVille($ville2);
        $manager->persist($lieu);

        $manager->flush();
    }
}
