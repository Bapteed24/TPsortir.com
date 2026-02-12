<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    // Option 1 (tu gardes TIME) :
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $duree = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motifAnnulation = null;

    #[ORM\Column]
    private ?int $nbInscriptionMax = null;

    // Reco: TEXT plutôt que 255
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoSortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    // IMPORTANT: Etat manquant (si UML le prévoit)
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organisateurSortie = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'sorties')]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDateHeureDebut(): ?\DateTimeImmutable { return $this->dateHeureDebut; }
    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static { $this->dateHeureDebut = $dateHeureDebut; return $this; }

    public function getDuree(): ?\DateTime { return $this->duree; }
    public function setDuree(\DateTime $duree): static { $this->duree = $duree; return $this; }

    public function getDateLimiteInscription(): ?\DateTimeImmutable { return $this->dateLimiteInscription; }
    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static { $this->dateLimiteInscription = $dateLimiteInscription; return $this; }

    public function getNbInscriptionMax(): ?int { return $this->nbInscriptionMax; }
    public function setNbInscriptionMax(int $nbInscriptionMax): static { $this->nbInscriptionMax = $nbInscriptionMax; return $this; }

    public function getInfoSortie(): ?string { return $this->infoSortie; }
    public function setInfoSortie(?string $infoSortie): static { $this->infoSortie = $infoSortie; return $this; }

    public function getCampus(): ?Campus { return $this->campus; }
    public function setCampus(?Campus $campus): static { $this->campus = $campus; return $this; }

    public function getLieu(): ?Lieu { return $this->lieu; }
    public function setLieu(?Lieu $lieu): static { $this->lieu = $lieu; return $this; }

    public function getEtat(): ?Etat { return $this->etat; }
    public function setEtat(?Etat $etat): static { $this->etat = $etat; return $this; }

    public function getOrganisateurSortie(): ?User { return $this->organisateurSortie; }
    public function setOrganisateurSortie(?User $organisateurSortie): static { $this->organisateurSortie = $organisateurSortie; return $this; }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection { return $this->participants; }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);


            $participant->addSorty($this);
        }
        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSorty($this);
        }
        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): self
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }
}
