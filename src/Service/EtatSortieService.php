<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;

class EtatSortieService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EtatRepository $etatRepository,
    ) {}

    public function getEtat(string $libelle): Etat
    {
        $etat = $this->etatRepository->findOneBy(['libelle' => $libelle]);

        if (!$etat) {
            throw new \RuntimeException("L'état '$libelle' n'existe pas");
        }

        return $etat;
    }




    public function appliquerTransitionsAutomatiques(Sortie $sortie): void
    {
        $now = new \DateTimeImmutable();
        $etatActuel = $sortie->getEtat()->getLibelle();


        if ($etatActuel === 'Ouverte') {
            if (
                $now > $sortie->getDateLimiteInscription()
                || $sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()



            ) {


                $sortie->setEtat($this->getEtat('Clôturée'));
            }
        }


        if (
            $sortie->getEtat()->getLibelle() === 'Clôturée'
            && $now >= $sortie->getDateHeureDebut()


        ) {



            $sortie->setEtat($this->getEtat('En cours'));
        }


        if   ($sortie->getEtat()->getLibelle() === 'En cours') {


            $dateFin = $this->calculerDateFin($sortie);
            if ($dateFin && $now >= $dateFin) {

                $sortie->setEtat($this->getEtat('Terminée'));
            }
        }

        $etatActuel = $sortie->getEtat()->getLibelle();
        if ($etatActuel === 'Terminée') {
            $dateHistorisation = $sortie->getDateHeureDebut()->modify('+1 month');
            if ($now >= $dateHistorisation) {
                $sortie->setEtat($this->getEtat('Historisée'));
            }}

    }

    private function calculerDateFin(Sortie $sortie): ?\DateTimeImmutable
    {
        $debut = $sortie->getDateHeureDebut();
        $duree = $sortie->getDuree(); // TIME

        if (!$debut || !$duree) {
            return null;
        }

        $h = (int) $duree->format('H');
        $m = (int) $duree->format('i');
        $s = (int) $duree->format('s');

        return $debut->modify("+$h hours +$m minutes +$s seconds");
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
