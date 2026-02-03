<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {

        // Campus

        $campus = new Campus();
        $campus->setName('Nantes');
        $manager->persist($campus);


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


        // Utilisateurs simples

        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setEmail("user$i@test.com");
            $user->setFirstname("User$i");
            $user->setName("Test");
            $user->setTelephone("061111111$i");
            $user->setRoles(['ROLE_USER']);
            $user->setIsActif(true);
            $user->setIsAdmin(false);
            $user->setCampus($campus);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
