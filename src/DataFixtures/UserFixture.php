<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['users'];
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getDependencies(): array
    {
        return [CampusFixtures::class, VilleFixtures::class];

    }

    public function load(ObjectManager $manager): void
    {

        $campus = $this->getReference('Campus1', Campus::class);

        // Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setFirstname('Admin');
        $admin->setName('Principal');
        $admin->setTelephone('0600000000');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActif(true);
        $admin->setIsAdmin(true);
        $admin->setCampus($campus);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $this->addReference('User0', $admin);

        // Utilisateurs simples

        for ($i = 1; $i <= 30; $i++) {
            $campusI = $this->getReference('Campus'.rand(1,3), Campus::class);
            $user = new User();
            $user->setEmail("user$i@test.com");
            $user->setFirstname("User$i");
            $user->setName("Test");


            $phone = '06'.str_pad(rand(1, 99999999),0, STR_PAD_LEFT);
            $user->setTelephone($phone);
            $user->setRoles(['ROLE_USER']);
            $user->setIsActif(true);
            $user->setIsAdmin(false);
            $user->setCampus($campusI);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            $this->addReference('User'.$i, $user);
        }

        $manager->flush();
    }
}
