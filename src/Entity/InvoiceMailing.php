<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoiceMailingRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceMailingRepository::class)]
class InvoiceMailing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceMailings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?DateTimeImmutable $mailedAt = null;

    #[ORM\ManyToOne]
    private ?User $mailedBy = null;

    #[ORM\OneToMany(targetEntity: InvoiceMailingRecipient::class, mappedBy: 'invoiceMailing', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoiceMailingRecipients;

    public function __construct()
    {
        $this->invoiceMailingRecipients = new ArrayCollection();
    }

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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getMailedAt(): ?DateTimeImmutable
    {
        return $this->mailedAt;
    }

    public function setMailedAt(DateTimeImmutable $mailedAt): static
    {
        $this->mailedAt = $mailedAt;

        return $this;
    }

    public function getMailedBy(): ?User
    {
        return $this->mailedBy;
    }

    public function setMailedBy(?User $mailedBy): static
    {
        $this->mailedBy = $mailedBy;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceMailingRecipient>
     */
    public function getInvoiceMailingRecipients(): Collection
    {
        return $this->invoiceMailingRecipients;
    }

    public function addInvoiceMailingRecipient(InvoiceMailingRecipient $invoiceMailingRecipient): static
    {
        if (!$this->invoiceMailingRecipients->contains($invoiceMailingRecipient)) {
            $this->invoiceMailingRecipients->add($invoiceMailingRecipient);
            $invoiceMailingRecipient->setInvoiceMailing($this);
        }

        return $this;
    }

    public function removeInvoiceMailingRecipient(InvoiceMailingRecipient $invoiceMailingRecipient): static
    {
        if ($this->invoiceMailingRecipients->removeElement($invoiceMailingRecipient)) {
            // set the owning side to null (unless already changed)
            if ($invoiceMailingRecipient->getInvoiceMailing() === $this) {
                $invoiceMailingRecipient->setInvoiceMailing(null);
            }
        }

        return $this;
    }
}
