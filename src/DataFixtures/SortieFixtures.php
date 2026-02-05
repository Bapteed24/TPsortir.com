<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{

    public static function getGroups(): array
    {
        return ['users'];
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
            EtatFixtures::class,
            VilleFixtures::class,
            LieuFixtures::class,
            UserFixture::class
        ];
    }

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 30; $i++) {
            $sortie = new Sortie();
            $dateDebut = $faker->dateTimeBetween('now', '+30 days');
            $dateDebut= \DateTimeImmutable::createFromMutable($dateDebut);
            $sortie->setName('sortie numéro : ' . $i);
            $sortie->setDateHeureDebut($dateDebut);
            $duration = new \DateTime('0000-00-00 00:08:00');
            $sortie->setDuree($duration);
            $dateFin = $faker->dateTimeBetween('+30 days', '+60 days');
            $dateFin= \DateTimeImmutable::createFromMutable($dateFin);
            $sortie->setDateLimiteInscription($dateFin);
            $sortie->setNbInscriptionMax(10);
            $sortie->setInfoSortie($faker->realText());
            $campus = $this->getReference('Campus'.rand(1,3), Campus::class);
            $sortie->setCampus($campus);
            $lieu = $this->getReference('lieu1', Lieu::class);
            $sortie->setLieu($lieu);
            $etat = $this->getReference('etat'.rand(1,7), Etat::class);
            $sortie->setEtat($etat);
            $user = $this->getReference('User'.rand(0,3), User::class);
            $sortie->setOrganisateurSortie($user);

            $manager->persist($sortie);
        }
        $manager->flush();
    }

//    private function addTrainers(Course $course): void {
//        for ($i=0; $i <= mt_rand(0,5); $i++) {
//            $trainer = $this->getReference('trainer'.rand(1,20), Trainer::class);
//            $course->addTrainer($trainer);
//        }
//    }

}
