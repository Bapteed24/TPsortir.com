<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        // Fake campus list (later: from DB)
        $campusOptions = [
            'CHARTRES DE BRETAGNE',
            'RENNES',
            'NANTES',
        ];

        // Fake filters (wired in UI only for now)
        $filters = [
            'campus' => (string) $request->query->get('campus', 'CHARTRES DE BRETAGNE'),
            'q' => trim((string) $request->query->get('q', '')),
            'from' => (string) $request->query->get('from', ''),
            'to' => (string) $request->query->get('to', ''),
            'mine' => (bool) $request->query->get('mine', false),
            'registered' => (bool) $request->query->get('registered', false),
            'not_registered' => (bool) $request->query->get('not_registered', false),
            'finished' => (bool) $request->query->get('finished', false),
        ];

        // Temporary fake "logged user"
        $participantNom = 'Jeannine L.';

        // Temporary fake data (later: Doctrine + business rules)
        $sorties = [
            [
                'id' => 1,
                'nom' => 'Philo',
                'dateHeureDebut' => '2026-02-10 19:00',
                'dateLimiteInscription' => '2026-02-08',
                'inscrits_count' => 8,
                'nbInscriptionsMax' => 8,
                'etat' => 'En cours',
                'estInscrit' => true,
                'organisateur' => ['id' => 101, 'pseudo' => 'Spinoz A.'],
                'actions' => ['Afficher'],
            ],
            [
                'id' => 2,
                'nom' => 'Origamie',
                'dateHeureDebut' => '2026-02-12 14:30',
                'dateLimiteInscription' => '2026-02-10',
                'inscrits_count' => 3,
                'nbInscriptionsMax' => 5,
                'etat' => 'Clôturée',
                'estInscrit' => true,
                'organisateur' => ['id' => 102, 'pseudo' => 'Rémi S.'],
                'actions' => ['Afficher'],
            ],
            [
                'id' => 3,
                'nom' => 'Perles',
                'dateHeureDebut' => '2026-02-15 10:00',
                'dateLimiteInscription' => '2026-02-14',
                'inscrits_count' => 2,
                'nbInscriptionsMax' => 12,
                'etat' => 'Clôturée',
                'estInscrit' => true,
                'organisateur' => ['id' => 103, 'pseudo' => 'Jojo56'],
                'actions' => ['Afficher', 'Se désister'],
            ],
            [
                'id' => 4,
                'nom' => 'Concert métal',
                'dateHeureDebut' => '2026-02-20 20:00',
                'dateLimiteInscriptioninscription' => '2026-02-19',
                'dateLimiteInscription' => '2026-02-19',
                'inscrits_count' => 8,
                'nbInscriptionsMax' => 10,
                'etat' => 'Ouverte',
                'estInscrit' => true,
                'organisateur' => ['id' => 104, 'pseudo' => 'Raymond D.'],
                'actions' => ['Afficher', 'Se désister'],
            ],
            [
                'id' => 5,
                'nom' => 'Jardinage',
                'dateHeureDebut' => '2026-02-22 09:00',
                'dateLimiteInscription' => '2026-02-21',
                'inscrits_count' => 3,
                'nbInscriptionsMax' => 5,
                'etat' => 'Ouverte',
                'estInscrit' => false,
                'organisateur' => ['id' => 105, 'pseudo' => 'Rémi S.'],
                'actions' => ['Afficher', "S'inscrire"],
            ],
            [
                'id' => 6,
                'nom' => 'Cinéma',
                'dateHeureDebut' => '2026-02-25 18:00',
                'dateLimiteInscription' => '2026-02-23',
                'inscrits_count' => 0,
                'nbInscriptionsMax' => 10,
                'etat' => 'En création',
                'estInscrit' => false,
                'organisateur' => ['id' => 999, 'pseudo' => 'Jeannine L.'],
                'actions' => ['Modifier', 'Publier', 'Supprimer'],
            ],
            [
                'id' => 7,
                'nom' => 'Pâte à sel',
                'dateHeureDebut' => '2026-02-28 15:00',
                'dateLimiteInscription' => '2026-02-26',
                'inscrits_count' => 0,
                'nbInscriptionsMax' => 5,
                'etat' => 'Ouverte',
                'estInscrit' => false,
                'organisateur' => ['id' => 999, 'pseudo' => 'Jeannine L.'],
                'actions' => ['Afficher', "S'inscrire", 'Annuler'],
            ],
        ];

        // Tiny fake filter: name contains
        if ($filters['q'] !== '') {
            $sorties = array_values(array_filter($sorties, fn($s) => stripos($s['nom'], $filters['q']) !== false));
        }

        return $this->render('sortie/list.html.twig', [
            'campusOptions' => $campusOptions,
            'filters' => $filters,
            'participantNom' => $participantNom,
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sortie/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id): Response
    {
        // Temporary fake data for detail
        $sortie = [
            'id' => $id,
            'nom' => 'Soirée jeux de société',
            'dateHeureDebut' => '2026-02-10 19:00',
            'dateLimiteInscription' => '2026-02-08',
            'nbInscriptionsMax' => 12,
            'dureeMinutes' => 180,
            'description' => 'On se retrouve pour jouer et boire un verre.',
            'ville' => 'Rennes',
            'campus' => 'Rennes',
            'rue' => '1 rue Exemple',
            'codePostal' => '35000',
            'latitude' => '48.1173',
            'longitude' => '-1.6778',
            'lieu' => ['nom' => 'Pub Murdock'],
            'organisateur' => ['id' => 10, 'pseudo' => 'Fabien'],
            'inscrits' => [
                ['id' => 101, 'pseudo' => 'JBL', 'prenom' => 'Jena-Baptiste', 'nom' => 'LAMARCK'],
                ['id' => 102, 'pseudo' => 'YC44', 'prenom' => 'Yves', 'nom' => 'COUSTEAU'],
                ['id' => 103, 'pseudo' => 'MC', 'prenom' => 'Michel', 'nom' => 'CONSTANT'],
            ],
        ];

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
