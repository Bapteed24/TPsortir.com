<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\SortieFormType;
use App\Repository\CampusRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Service\EtatSortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_list', methods: ['GET'])]
    public function list(
        Request $request,
        CampusRepository $campusRepository,
        SortieRepository $sortieRepository,
        \App\Repository\VilleRepository $villeRepository,
        EtatSortieService $etatSortieService
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

        $campusOptions = $campusRepository->findAll();
        $villeOptions = $villeRepository->findAll();

        $defaultCampus = ($user && $user->getCampus()) ? $user->getCampus()->getId() : '';


        $filters = [
            'campus' => $request->query->get('campus', $defaultCampus),
            'ville' => $request->query->get('ville', ''),
            'q' => trim((string) $request->query->get('q', '')),
            'from' => (string) $request->query->get('from', ''),
            'to' => (string) $request->query->get('to', ''),
            'mine' => (bool) $request->query->get('mine', false),
            'registered' => (bool) $request->query->get('registered', false),
            'not_registered' => (bool) $request->query->get('not_registered', false),
            'finished' => (bool) $request->query->get('finished', false),
        ];



        $allSorties = $sortieRepository->listAccueil($user);


        foreach ($allSorties as $s) {
            $etatSortieService->appliquerTransitionsAutomatiques($s);
        }
        $etatSortieService->flush();


        $user = $this->getUser();

        $sorties = array_filter($allSorties, function ($sortie) use ($filters, $user) {

            if (!empty($filters['campus']) && $sortie->getCampus()->getId() != $filters['campus']) {
                return false;
            }


            if (!empty($filters['ville']) && $sortie->getLieu()->getVille()->getId() != $filters['ville']) {
                return false;
            }


            if (!empty($filters['q']) && stripos($sortie->getName(), $filters['q']) === false) {
                return false;
            }


            if (!empty($filters['from']) && $sortie->getDateHeureDebut() < new \DateTime($filters['from'])) {
                return false;
            }
            if (!empty($filters['to']) && $sortie->getDateHeureDebut() > new \DateTime($filters['to'] . ' 23:59:59')) {
                return false;
            }


            if ($user) {

                if ($filters['mine'] && $sortie->getOrganisateurSortie() !== $user) {
                    return false;
                }


                if ($filters['registered'] && !$sortie->getParticipants()->contains($user)) {
                    return false;
                }


                if ($filters['not_registered'] && $sortie->getParticipants()->contains($user)) {
                    return false;
                }
            }

            if ($filters['finished'] && $sortie->getEtat()->getLibelle() !== 'Passée') {
                return false;
            }

            return true;
        });

        return $this->render('sortie/list.html.twig', [
            'campusOptions' => $campusOptions,
            'villeOptions' => $villeOptions,
            'filters' => $filters,
            'participantNom' => $this->getUser()?->getFirstname(),
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sortie/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Sortie $sortie): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/creer', name: 'sortie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

        $sortie = new Sortie();

        $form = $this->createForm(SortieFormType::class, $sortie, [
            'show_organisateurSortie' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException();
            }
            $sortie->setCampus($user->getCampus());
            $sortie->setOrganisateurSortie($user);
            $user->addSorty($sortie);

            $action = $request->request->get('action', 'draft');
            if ($action === 'publish') {
                $sortie->setEtat($etatSortieService->getEtat('Ouverte'));
            } else {
                $sortie->setEtat($etatSortieService->getEtat('En création'));
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sortie/{id}/modifier', name: 'sortie_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response {
        /** @var User|null $user */
        if (!$sortie) return $this->redirectToRoute('sortie_list');
            ($etatSortieService->getEtat($sortie->getEtat()->getLibelle())) ?? $this->redirectToRoute('sortie_list');
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $isOrganisateur = $sortie->getOrganisateurSortie()?->getId() === $user->getId();
        if (!$isOrganisateur && !$user->isAdmin()) {
            throw $this->createAccessDeniedException();
        }

        // 🔒 Modifiable uniquement si "En création"
        if ($sortie->getEtat()->getLibelle() !== 'En création') {
            $this->addFlash('info', 'Cette sortie ne peut plus être modifiée car elle n’est pas en état "En création".');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $form = $this->createForm(SortieFormType::class, $sortie, [
            'show_organisateurSortie' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $request->request->get('action', 'save'); // save / publish / delete

            if ($action === 'delete') {
                $entityManager->remove($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Sortie supprimée ✅');
                return $this->redirectToRoute('sortie_list');
            }

            if ($action === 'publish') {
                $sortie->setEtat($etatSortieService->getEtat('Ouverte'));
                $entityManager->flush();

                $this->addFlash('success', 'Sortie publiée ✅');
                return $this->redirectToRoute('sortie_list');
            }
// save => reste "En création"
            $sortie->setEtat($etatSortieService->getEtat('En création'));
            $entityManager->flush();

            $this->addFlash('success', 'Sortie enregistrée (En création) ✅');
            return $this->redirectToRoute('sortie_modifier', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/modifier.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }




    #[Route('/sortie/{id}/inscrire', name: 'sortie_inscrire', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function inscrire(

        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $etatSortieService->appliquerTransitionsAutomatiques($sortie);
        $etatSortieService->flush();

        if ($user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu es déjà inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', 'Inscription impossible : sortie non ouverte.');
            return $this->redirectToRoute('sortie_list');
        }

        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            $this->addFlash('danger', 'La date limite d’inscription est dépassée.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()) {
            $this->addFlash('danger', 'La sortie est complète.');
            return $this->redirectToRoute('sortie_list');
        }

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
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $etatSortieService->appliquerTransitionsAutomatiques($sortie);
        $etatSortieService->flush();

        if (!$user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu n’es pas inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

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
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

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

    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function annuler(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatSortieService $etatSortieService,
        Request $request,
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user->isActif() === false) {
            return $this->redirectToRoute('sortie_innactif');
        }

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

        if ($sortie->getEtat()->getLibelle() === 'En cours') {
            $this->addFlash('danger', 'Annulation impossible : La sortie à commencée.');
            return $this->redirectToRoute('sortie_list');
        }


        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', 'Annulation impossible : la sortie n’est pas ouverte.');
            return $this->redirectToRoute('sortie_list');
        }

        $motif = trim((string) $request->request->get('motif'));
        if ($motif === '') {
            $this->addFlash('danger', 'Motif obligatoire pour annuler la sortie.');
            return $this->redirectToRoute('sortie_list');

        }

        $sortie->setMotifAnnulation($motif);


        $sortie->setEtat($etatSortieService->getEtat('Annulée'));
        $entityManager->flush();

        $this->addFlash('success', 'Sortie annulée ✅');
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/compteinactif', name: 'sortie_innactif', methods: ['GET'])]

    public function compteinactif(){
            $user = $this->getUser();
            if (!$user->isActif() === false) {
                return $this->redirectToRoute('sortie_list');
            }
            return $this->render('sortie/compte_inactif.html.twig', []);
    }

    #[Route('/ajax/lieu/{id}', name: 'lieu_detail_ajax', methods: ['GET'])]
    public function LieuDetailAjax(Lieu $lieu, LieuRepository $lieuRepository): Response
    {

        # methode a changer
        $lieu2 = $lieuRepository->getByIdAjax($lieu->getId());
        return $this->json($lieu2, 200, [], [
            'groups' => ['post:read'],
        ]);
    }

#[Route('/ajax/ville/{id}', name: 'ville_ajax', methods: ['GET'])]
public function VilleAjax(Ville $ville, LieuRepository $lieuRepository): Response
{

    # methode a changer
    $lieu2 = $lieuRepository->findBy(
        ['ville' => $ville->getId()]
    );
    return $this->json($lieu2, 200, [], [
        'groups' => ['post:read'],
    ]);
}

}