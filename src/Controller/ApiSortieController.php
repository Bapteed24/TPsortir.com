<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Attribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


final class ApiSortieController extends AbstractController
{

    public function __invoke(
        SortieRepository $sortieRepository,
        Request $request
    ): JsonResponse {
        $parameters = [];
        $etat = $request->query->get('etat');

        if ($etat) {
            $parameters['etat'] = $etat;
        }

        $sortie = $sortieRepository->apiList($parameters);

        return $this->json($sortie, 200, [], [
            'groups' => ['public'],
        ]);
    }
}
