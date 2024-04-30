<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\TermOfPaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TermOfPaymentRepository::class)]
class TermOfPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $dueDays = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textEn = null;

    #[ORM\ManyToOne(inversedBy: 'termOfPayments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Principal $principal = null;

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

    public function getDueDays(): ?int
    {
        return $this->dueDays;
    }

    public function setDueDays(int $dueDays): static
    {
        $this->dueDays = $dueDays;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getTextEn(): ?string
    {
        return $this->textEn;
    }

    public function setTextEn(string $textEn): static
    {
        $this->textEn = $textEn;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getPrincipal(): ?Principal
    {
        return $this->principal;
    }

    public function setPrincipal(?Principal $principal): static
    {
        $this->principal = $principal;

        return $this;
    }
}
