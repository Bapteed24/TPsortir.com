<?php

namespace App\Controller;

use App\Form\SortieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Entity\Sortie;
use App\Entity\User;
use App\Service\EtatSortieService;
class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_list', methods: ['GET'])]
    public function list(
        Request $request,
        CampusRepository $campusRepository,
        SortieRepository $sortieRepository,
        EtatSortieService $etatSortieService
    ): Response
    {

        $campusOptions = $campusRepository->findAll();


        $filters = [
            'campus' => $request->query->get('campus', ''),
            'q' => trim((string) $request->query->get('q', '')),
            'from' => (string) $request->query->get('from', ''),
            'to' => (string) $request->query->get('to', ''),
            'mine' => (bool) $request->query->get('mine', false),
            'registered' => (bool) $request->query->get('registered', false),
            'not_registered' => (bool) $request->query->get('not_registered', false),
            'finished' => (bool) $request->query->get('finished', false),
        ];


        $sorties = $sortieRepository->findAll();


        foreach ($sorties as $s) {
            $etatSortieService->appliquerTransitionsAutomatiques($s);
        }
        $etatSortieService->flush();
        return $this->render('sortie/list.html.twig', [
            'campusOptions' => $campusOptions,
            'filters' => $filters,
            'participantNom' => $this->getUser()?->getFirstname(),
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sortie/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Sortie $sortie): Response
    {
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/creer', name: 'sortie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieFormType::class, $sortie, [
            'show_organisateurSortie' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException();
            }

            // organisateur
            $sortie->setOrganisateurSortie($user);

            // Deux boutons
            $action = $request->request->get('action', 'draft');

            if ($action === 'publish') {
                $sortie->setEtat($etatSortieService->getEtat('Ouverte'));
            } else {
                $sortie->setEtat($etatSortieService->getEtat('En création'));
            }



            $user->addSorty($sortie);

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    #[Route('/sortie/{id}/inscrire', name: 'sortie_inscrire', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function inscrire(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // ✅ Mise à jour auto de l’état avant toute logique
        $etatSortieService->appliquerTransitionsAutomatiques($sortie);
        $etatSortieService->flush();

        // Déjà inscrit ?
        if ($user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu es déjà inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        // Autorisé uniquement si Ouverte
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', 'Inscription impossible : sortie non ouverte.');
            return $this->redirectToRoute('sortie_list');
        }

        // Date limite
        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            $this->addFlash('danger', 'La date limite d’inscription est dépassée.');
            return $this->redirectToRoute('sortie_list');
        }

        // Complet
        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()) {
            $this->addFlash('danger', 'La sortie est complète.');
            return $this->redirectToRoute('sortie_list');
        }

        // Inscription (owning side)
        $user->addSorty($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription effectuée ✅');
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{id}/desister', name: 'sortie_desister', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function desister(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // ✅ Mise à jour auto de l’état avant toute logique
        $etatSortieService->appliquerTransitionsAutomatiques($sortie);
        $etatSortieService->flush();

        // Pas inscrit ?
        if (!$user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu n’es pas inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        // Désistement autorisé si Ouverte OU Clôturée
        $lib = $sortie->getEtat()->getLibelle();
        if (!in_array($lib, ['Ouverte', 'Clôturée'], true)) {
            $this->addFlash('danger', 'Désistement impossible pour cet état.');
            return $this->redirectToRoute('sortie_list');
        }

        $user->removeSorty($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Désinscription effectuée ✅');
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{id}/publier', name: 'sortie_publier', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function publier(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }


        $isOrganisateur = $sortie->getOrganisateurSortie()?->getId() === $user->getId();
        if (!$isOrganisateur && !$user->isAdmin()) {
            throw $this->createAccessDeniedException();
        }


        if ($sortie->getEtat()->getLibelle() !== 'En création') {
            $this->addFlash('info', 'Cette sortie ne peut plus être publiée.');
            return $this->redirectToRoute('sortie_list');
        }

        $sortie->setEtat($etatSortieService->getEtat('Ouverte'));
        $entityManager->flush();

        $this->addFlash('success', 'Sortie publiée ✅');
        return $this->redirectToRoute('sortie_list');
    }
    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function annuler(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }


        $isOrganisateur = $sortie->getOrganisateurSortie()?->getId() === $user->getId();
        if (!$isOrganisateur && !$user->isAdmin()) {
            throw $this->createAccessDeniedException();
        }


        $etatSortieService->appliquerTransitionsAutomatiques($sortie);
        $etatSortieService->flush();


        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', 'Annulation impossible : la sortie n’est pas ouverte.');
            return $this->redirectToRoute('sortie_list');
        }


        foreach ($sortie->getParticipants() as $p) {
            if ($sortie->getOrganisateurSortie() && $p->getId() !== $sortie->getOrganisateurSortie()->getId()) {
                $this->addFlash('danger', 'Annulation impossible : des participants sont déjà inscrits.');
                return $this->redirectToRoute('sortie_list');
            }
        }


        $sortie->setEtat($etatSortieService->getEtat('Annulée'));
        $entityManager->flush();

        $this->addFlash('success', 'Sortie annulée ✅');
        return $this->redirectToRoute('sortie_list');
    }
}
