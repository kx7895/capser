<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoiceTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceTypeRepository::class)]
class InvoiceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $type = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $typeEn = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $typeFr = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $typeIt = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameFr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameIt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeEn(): ?string
    {
        return $this->typeEn;
    }

    public function setTypeEn(?string $typeEn): static
    {
        $this->typeEn = $typeEn;

        return $this;
    }

    public function getTypeFr(): ?string
    {
        return $this->typeFr;
    }

    public function setTypeFr(?string $typeFr): static
    {
        $this->typeFr = $typeFr;

        return $this;
    }

    public function getTypeIt(): ?string
    {
        return $this->typeIt;
    }

    public function setTypeIt(?string $typeIt): static
    {
        $this->typeIt = $typeIt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): static
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(?string $nameFr): static
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getNameIt(): ?string
    {
        return $this->nameIt;
    }

    public function setNameIt(?string $nameIt): static
    {
        $this->nameIt = $nameIt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getType();
    }
}
