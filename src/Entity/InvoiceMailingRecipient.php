<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoiceMailingRecipientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceMailingRecipientRepository::class)]
class InvoiceMailingRecipient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceMailingRecipients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InvoiceMailing $invoiceMailing = null;

    #[ORM\Column(length: 255)]
    private ?string $emailAddress = null;

    #[ORM\Column(length: 3)]
    private ?string $emailAddressType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceMailing(): ?InvoiceMailing
    {
        return $this->invoiceMailing;
    }

    public function setInvoiceMailing(?InvoiceMailing $invoiceMailing): static
    {
        $this->invoiceMailing = $invoiceMailing;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getEmailAddressType(): ?string
    {
        return $this->emailAddressType;
    }

    public function setEmailAddressType(string $emailAddressType): static
    {
        $this->emailAddressType = $emailAddressType;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getEmailAddress().' ('.$this->getEmailAddressType().')';
    }
}
