<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\CustomerNoteAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerNoteAttachmentRepository::class)]
class CustomerNoteAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'customerNoteAttachments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerNote $customerNote = null;

    #[ORM\Column(length: 255)]
    private ?string $niceFilename = null;

    #[ORM\Column(length: 500)]
    private ?string $storageFilename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerNote(): ?CustomerNote
    {
        return $this->customerNote;
    }

    public function setCustomerNote(?CustomerNote $customerNote): static
    {
        $this->customerNote = $customerNote;

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

    public function __toString(): string
    {
        return $this->getNiceFilename();
    }
}
