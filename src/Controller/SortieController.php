<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Entity\Sortie;


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

    #[Route('/sortie/creer', name: 'sortie_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('sortie/create.html.twig', [
            'campus' => 'CHARTRES DE BRETAGNE',
            'lieux' => [
                ['id' => 1, 'nom' => 'Pub Murdock'],
                ['id' => 2, 'nom' => 'Parc du Thabor'],
            ],
        ]);
    }
}
