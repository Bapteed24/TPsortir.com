<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/{id}', name: 'participant_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        // Temporary fake data
        $participant = [
            'id' => $id,
            'pseudo' => 'Sophie',
            'prenom' => 'Sophie',
            'nom' => 'L.',
            'email' => 'sophie@example.com',
            'telephone' => '0600000000',
            'campus' => 'Rennes',
        ];

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }
}
