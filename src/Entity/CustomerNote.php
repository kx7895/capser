<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\CustomerNoteRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerNoteRepository::class)]
class CustomerNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'customerNotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: CustomerNoteAttachment::class, mappedBy: 'customerNote', orphanRemoval: true)]
    private Collection $customerNoteAttachments;

    public function __construct()
    {
        $this->customerNoteAttachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

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

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, CustomerNoteAttachment>
     */
    public function getCustomerNoteAttachments(): Collection
    {
        return $this->customerNoteAttachments;
    }

    public function addCustomerNoteAttachment(CustomerNoteAttachment $customerNoteAttachment): static
    {
        if (!$this->customerNoteAttachments->contains($customerNoteAttachment)) {
            $this->customerNoteAttachments->add($customerNoteAttachment);
            $customerNoteAttachment->setCustomerNote($this);
        }

        return $this;
    }

    public function removeCustomerNoteAttachment(CustomerNoteAttachment $customerNoteAttachment): static
    {
        if ($this->customerNoteAttachments->removeElement($customerNoteAttachment)) {
            // set the owning side to null (unless already changed)
            if ($customerNoteAttachment->getCustomerNote() === $this) {
                $customerNoteAttachment->setCustomerNote(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
