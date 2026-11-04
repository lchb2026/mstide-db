<?php

namespace App\Entity;

use App\Entity\Type;

use App\Repository\CompoundRepository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CompoundRepository::class)]
#[ORM\Table(name: "compounds")]
class Compound
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $abbr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $molecularFormula = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $compoundId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $instrument = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $scanType = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ionization = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $declusteringPotential = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $collisionEnergy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonicalSmiles = null;

    #[ORM\Column(nullable: true)]
    private ?array $massTable = null;

    #[ORM\ManyToMany(targetEntity: Fragment::class, mappedBy: "compounds")]
    private Collection $fragments;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $sdf = null;

    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: "compounds")]
    #[ORM\JoinTable(
        name: "compounds_types",
        joinColumns: [new ORM\JoinColumn(name: "compound_id", referencedColumnName: "id", onDelete: "CASCADE")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "type_id", referencedColumnName: "id", onDelete: "CASCADE")]
    )]
    private Collection $types;

    public function __construct()
    {
        $this->fragments = new ArrayCollection();
        $this->types = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAbbr(): ?string
    {
        return $this->abbr;
    }

    public function setAbbr(?string $abbr): static
    {
        $this->abbr = $abbr;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMolecularFormula(): ?string
    {
        return $this->molecularFormula;
    }

    public function setMolecularFormula(?string $molecularFormula): static
    {
        $this->molecularFormula = $molecularFormula;

        return $this;
    }

    public function getCompoundId(): ?string
    {
        return $this->compoundId;
    }

    public function setCompoundId(?string $compoundId): static
    {
        $this->compoundId = $compoundId;

        return $this;
    }

    public function getInstrument(): ?string
    {
        return $this->instrument;
    }

    public function setInstrument(?string $instrument): static
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function getScanType(): ?string
    {
        return $this->scanType;
    }

    public function setScanType(?string $scanType): static
    {
        $this->scanType = $scanType;

        return $this;
    }

    public function getIonization(): ?string
    {
        return $this->ionization;
    }

    public function setIonization(?string $ionization): static
    {
        $this->ionization = $ionization;

        return $this;
    }

    public function getDeclusteringPotential(): ?string
    {
        return $this->declusteringPotential;
    }

    public function setDeclusteringPotential(?string $declusteringPotential): static
    {
        $this->declusteringPotential = $declusteringPotential;

        return $this;
    }

    public function getCollisionEnergy(): ?string
    {
        return $this->collisionEnergy;
    }

    public function setCollisionEnergy(?string $collisionEnergy): static
    {
        $this->collisionEnergy = $collisionEnergy;

        return $this;
    }

    public function getCanonicalSmiles(): ?string
    {
        return $this->canonicalSmiles;
    }

    public function setCanonicalSmiles(?string $canonicalSmiles): static
    {
        $this->canonicalSmiles = $canonicalSmiles;

        return $this;
    }

    public function getMassTable(): ?array
    {
        return $this->massTable;
    }

    public function setMassTable(?array $massTable): static
    {
        $this->massTable = $massTable;

        return $this;
    }

    /** @return Collection<int, Fragment> */
    public function getFragments(): Collection
    {
        return $this->fragments;
    }

    public function addFragment(Fragment $fragment): self
    {
        if (!$this->fragments->contains($fragment)) {
            $this->fragments[] = $fragment;
            $fragment->addCompound($this);
        }
        return $this;
    }

    public function removeFragment(Fragment $fragment): self
    {
        if ($this->fragments->removeElement($fragment)) {
            $fragment->removeCompound($this);
        }
        return $this;
    }

    public function clearFragments(): self
    {
        foreach ($this->fragments as $fragment) {
            $fragment->removeCompound($this);
        }
        $this->fragments = new ArrayCollection();
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;
        return $this;
    }

    public function getSDF(): ?string
    {
        return $this->sdf;
    }

    public function setSDF(?string $sdf): self
    {
        $this->sdf = $sdf;
        return $this;
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(Type $type): self
    {
        if (!$this->types->contains($type)) {
            $this->types[] = $type;
            $type->addCompound($this);
        }
        return $this;
    }

    public function removeType(Type $type): self
    {
        if ($this->types->removeElement($type)) {
            $type->removeCompound($this);
        }
        return $this;
    }

    public function clearTypes(): self
    {
        foreach ($this->types as $type) {
            $type->removeCompound($this);
        }
        $this->types = new ArrayCollection();
        return $this;
    }
}
