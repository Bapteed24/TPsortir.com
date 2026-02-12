<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CsvUserImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private CampusRepository $campusRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {}

    public function import(string $filePath): array
    {
        $created = 0;
        $errors = [];

        if (!file_exists($filePath)) {
            return ['created' => 0, 'errors' => ['File not found']];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['created' => 0, 'errors' => ['Cannot open file']];
        }

        $line = 1;

        // Skip header
        fgetcsv($handle, 0, ';');

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $line++;

            // Skip empty lines
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            // Validate column count
            if (count($data) < 8) {
                $errors[] = "Ligne $line : format invalide (8 colonnes attendues)";
                continue;
            }

            try {
                // NOTE: 6e colonne = campus NAME (ex: "Campus 1")
                [$email, $username, $firstname, $name, $telephone, $campusName, $isAdmin, $isActif] = $data;

                $email = trim((string) $email);
                $username = trim((string) $username);
                $firstname = trim((string) $firstname);
                $name = trim((string) $name);
                $telephone = trim((string) $telephone);
                $campusName = trim((string) $campusName);
                $isAdmin = (int) trim((string) $isAdmin);
                $isActif = (int) trim((string) $isActif);

                if ($email === '') {
                    $errors[] = "Ligne $line : email manquant";
                    continue;
                }

                // Duplicate email check
                if ($this->userRepository->findOneBy(['email' => $email])) {
                    $errors[] = "Ligne $line : email déjà utilisé ($email)";
                    continue;
                }

                // Username fallback
                if ($username === '') {
                    $username = explode('@', $email)[0] ?? '';
                }
                if ($username === '') {
                    $errors[] = "Ligne $line : username manquant";
                    continue;
                }

                // Duplicate username check (if your DB requires it unique)
                if ($this->userRepository->findOneBy(['username' => $username])) {
                    $errors[] = "Ligne $line : username déjà utilisé ($username)";
                    continue;
                }

                if ($campusName === '') {
                    $errors[] = "Ligne $line : campus manquant";
                    continue;
                }

                // ✅ Find campus by NAME (adapt field name if needed)
                $campus = $this->campusRepository->findOneBy(['name' => $campusName]);
                // If your field is "nom", replace the line above with:
                // $campus = $this->campusRepository->findOneBy(['nom' => $campusName]);

                if (!$campus) {
                    $errors[] = "Ligne $line : campus introuvable ($campusName)";
                    continue;
                }

                $user = new User();
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setFirstname($firstname);
                $user->setName($name);
                $user->setTelephone($telephone);
                $user->setCampus($campus);
                $user->setIsAdmin($isAdmin === 1);
                $user->setIsActif($isActif === 1);

                // Default password
                $plainPassword = 'Temp123!';
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, $plainPassword)
                );

                $this->em->persist($user);
                $created++;

            } catch (\Throwable $e) {
                $errors[] = "Ligne $line : " . $e->getMessage();
            }
        }

        fclose($handle);

        $this->em->flush();

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }
}
