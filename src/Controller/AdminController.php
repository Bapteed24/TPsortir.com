<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/utilisateur/list', name: 'app_admin_user_list')]
    public function adminUserList(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/user/list.html.twig', [
            'users' => $users,
        ]);

    }

    #[Route('/utilisateur/ajouter', name: 'app_admin_user_create')]
    public function adminUserCreate(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
        ): Response
    {
        $user = new User();
        // Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        // Gestion de la requête
        $form->handleRequest($request);
        // Soumission + validation

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_list');
        }

        // Affichage du formulaire
        return $this->render('admin/user/create.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/modifier/{id}', name: 'adminUserUpdate', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function adminUserUpdate(
        Request $request,
        EntityManagerInterface $entityManager,
        User $user,
    ): Response
    {

        // Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        // Gestion de la requête
        $form->handleRequest($request);
        // Soumission + validation

        if ($form->isSubmitted() && $form->isValid()) {
            // ⚠️ Ici tu hash normalement le mot de passe
            // (je peux te le rajouter si tu veux)

            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_user_list');
        }

        // Affichage du formulaire
        return $this->render('admin/user/create.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/supprimer/{id}', name: 'adminUserDelete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function adminUserDelete(
        Request $request,
        EntityManagerInterface $entityManager,
        User $user,
    ): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        // Redirection après inscription
        return $this->redirectToRoute('app_admin_user_list');
    }

    #[Route('/sortie/ajouter', name: 'app_admin_sortie_create')]
    public function createSortie(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/sortie/modifier', name: 'app_admin_sortie_update')]
    public function UpdateSortie(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

}
