<?php

namespace App\Controller;

use App\Form\SortieFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Entity\Sortie;
use App\Entity\User;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_list', methods: ['GET'])]
    public function list(
        Request $request,
        CampusRepository $campusRepository,
        SortieRepository $sortieRepository
    ): Response
    {
        // 1) campus depuis la DB
        $campusOptions = $campusRepository->findAll();

        // 2) filtres
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

        // 3) sorties depuis la DB (simple, on filtre après)
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            'campusOptions' => $campusOptions,
            'filters' => $filters,
            'participantNom' => $this->getUser()?->getFirstname(), // si ton User/Participant a firstname
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
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


            $sortie->setOrganisateurSortie($user);


            $user->addSorty($sortie);



            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ...

    #[Route('/sortie/{id}/inscrire', name: 'sortie_inscrire', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function inscrire(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Déjà inscrit ?
        if ($user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu es déjà inscrit à cette sortie.');
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
    public function desister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Pas inscrit ?
        if (!$user->getSorties()->contains($sortie)) {
            $this->addFlash('info', 'Tu n’es pas inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        $user->removeSorty($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Désinscription effectuée ✅');
        return $this->redirectToRoute('sortie_list');
    }

}
