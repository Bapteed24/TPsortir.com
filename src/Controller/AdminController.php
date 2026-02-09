<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieFormType;
use App\Repository\RegistrationFormType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

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
        return $this->render('admin/user/update.html.twig', [
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


    #[Route('/sortie/list', name: 'app_admin_sortie_list')]
    public function listSortie(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('admin/sortie/list.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sortie/ajouter', name: 'app_admin_sortie_create')]
    public function createSortie(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
//        $user = $this->getUser();
        $sortie  = new Sortie();
        // Création du formulaire
        $form = $this->createForm(SortieFormType::class, $sortie);
        // Gestion de la requête
        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
//            $sortie->setOrganisateurSortie($user);
            $entityManager->persist($sortie);
            $entityManager->flush();

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_sortie_list');
        }

        // Affichage du formulaire
        return $this->render('admin/sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sortie/modifier/{id}', name: 'app_admin_sortie_update')]
    public function UpdateSortie(
        EntityManagerInterface $entityManager,
        Request $request,
        Sortie $sortie,
    ): Response
    {
        // Création du formulaire
        $form = $this->createForm(SortieFormType::class, $sortie);
        // Gestion de la requête
        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_sortie_list');
        }

        // Affichage du formulaire
        return $this->render('admin/sortie/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sortie/supprimer/{id}', name: 'app_admin_sortie_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteSortie(
        EntityManagerInterface $entityManager,
        Sortie $sortie,
    ): Response
    {
        $entityManager->remove($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_sortie_list');
    }


}
