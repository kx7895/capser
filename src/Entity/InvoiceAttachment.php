<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoiceAttachmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceAttachmentRepository::class)]
class InvoiceAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceAttachments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $niceFilename = null;

    #[ORM\Column(length: 500)]
    private ?string $storageFilename = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getNiceFilename(): ?string
    {
        return $this->niceFilename;
    }

    public function setNiceFilename(string $niceFilename): static
    {
        $this->niceFilename = $niceFilename;

        return $this;
    }

    public function getStorageFilename(): ?string
    {
        return $this->storageFilename;
    }

    public function setStorageFilename(string $storageFilename): static
    {
        $this->storageFilename = $storageFilename;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getNiceFilename();
    }
}
