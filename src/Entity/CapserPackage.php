<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\CapserPackageRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapserPackageRepository::class)]
class CapserPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $validFrom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $validTo = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getValidFrom(): ?DateTimeInterface
    {
        return $this->validFrom;
    }

    public function setValidFrom(DateTimeInterface $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidTo(): ?DateTimeInterface
    {
        return $this->validTo;
    }

    public function setValidTo(?DateTimeInterface $validTo): static
    {
        $this->validTo = $validTo;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
