<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\CampusFormType;
use App\Form\RegistrationFormType;
use App\Form\SortieFormType;
use App\Form\VilleFormType;
use App\Repository\CampusRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
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

    #[Route('/utilisateur/supprimer/{id}', name: 'app_admin_user_delete', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function adminUserDelete(
        Request $request,
        EntityManagerInterface $entityManager,
        User $user,
        UserRepository $userRepository,
    ): Response
    {
        $errors = 0;

        $userWithSortie = $userRepository->checkUserWithSortie($user->getId());

        if ($userWithSortie) {
            $errors++;
            $this->addFlash('danger', "Impossible de supprimer l'utisateur'. Supprimer les sorties lié pour pouvoir le supprimer. Préférer mettre le compte en inactif.");
        }
        if ($errors > 0) {
            return $this->redirectToRoute('app_admin_user_list');
        }
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
        $sortie  = new Sortie();
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

    #[Route('/campus/list', name: 'app_admin_campus_list')]
    public function listeCampus(CampusRepository $campusRepository): Response
    {
        $campus = $campusRepository->findAll();
        return $this->render('admin/campus/list.html.twig', [
            'campus' => $campus,
        ]);
    }

    #[Route('/campus/ajouer', name: 'app_admin_campus_create')]
    public function createCampus(
        CampusRepository $campusRepository,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $campus  = new Campus();
        // Création du formulaire
        $form = $this->createForm(CampusFormType::class, $campus);

        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            // flash message
            $this->addFlash('success', 'Le campus a bien été crée ✅');

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_campus_list');
        }

        $campus = $campusRepository->findAll();
        return $this->render('admin/campus/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/campus/modifier/{id}', name: 'app_admin_campus_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function udapteCampus(
        CampusRepository $campusRepository,
        Request $request,
        Campus $campus,
        EntityManagerInterface $entityManager): Response
    {

        // Création du formulaire
        $form = $this->createForm(CampusFormType::class, $campus);

        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            // flash message
            $this->addFlash('success', 'Le campus a bien été modifié ✅');

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_campus_list');
        }

        $campus = $campusRepository->findAll();
        return $this->render('admin/campus/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/campus/supprimer/{id}', name: 'app_admin_campus_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteCampus(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Campus $campus,
    ): Response
    {
        $error = 0;

        $users = $userRepository->findBy(["campus" => $campus->getId()]);
        if ($users) {
            $error++;
            $this->addFlash('danger', 'Impossible de supprimer le campus. Veuillez supprimer les utilisateurs liés ou leur attribué un autre campus.');
        }

        if ($error > 0) {
            return $this->redirectToRoute('app_admin_campus_list');
        }

        $entityManager->remove($campus);
        $entityManager->flush();

        // flash message
        $this->addFlash('success', 'Le campus a bien été supprimer ✅');

        return $this->redirectToRoute('app_admin_campus_list');
    }


    #[Route('/ville/list', name: 'app_admin_ville_list')]
    public function villeCampus(VilleRepository $villeRepository): Response
    {
        $ville = $villeRepository->findAll();
        return $this->render('admin/ville/list.html.twig', [
            'villes' => $ville,
        ]);
    }

    #[Route('/ville/ajouer', name: 'app_admin_ville_create')]
    public function createVille(
        CampusRepository $campusRepository,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $ville  = new ville();
        // Création du formulaire
        $form = $this->createForm(VilleFormType::class, $ville);

        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            // flash message
            $this->addFlash('success', 'La ville a bien été crée ✅');

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_ville_list');
        }

        $campus = $campusRepository->findAll();
        return $this->render('admin/ville/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ville/modifier/{id}', name: 'app_admin_ville_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function updateVille(
        Request $request,
        Ville $ville,
        EntityManagerInterface $entityManager): Response
    {

        // Création du formulaire
        $form = $this->createForm(VilleFormType::class, $ville);

        $form->handleRequest($request);
        // Soumission + validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            // flash message
            $this->addFlash('success', 'La ville a bien été modifié ✅');

            // Redirection après inscription
            return $this->redirectToRoute('app_admin_ville_list');
        }

        return $this->render('admin/ville/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ville/supprimer/{id}', name: 'app_admin_ville_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteVille(
        EntityManagerInterface $entityManager,
        Ville $ville,
        LieuRepository $lieuRepository,
    ): Response
    {
        $error = 0;
        $lieux = $lieuRepository->findBy(["ville" => $ville->getId()]);
        if ($lieux > 0) {
            $error++;
            $this->addFlash('danger', 'Impossible de supprimer la viller. Veuillez supprimer lieux liés.');
        }
        if ($error > 0) {
            return $this->redirectToRoute('app_admin_ville_list');
        }



        $entityManager->remove($ville);
        $entityManager->flush();

        // flash message
        $this->addFlash('success', 'La ville a bien été supprimer ✅');

        return $this->redirectToRoute('app_admin_ville_list');
    }
}
