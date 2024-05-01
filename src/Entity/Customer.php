<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\CustomerRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[HasLifecycleCallbacks]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Principal $principal = null;

    #[ORM\ManyToOne]
    private ?CustomerType $customerType = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine4 = null;

    #[ORM\ManyToOne]
    private ?Country $addressLineCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vatId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $vatExemptInvoicesAllowed = null;

    #[ORM\ManyToOne]
    private ?AccountingPlanLedger $accountingPlanLedgerDefault = null;

    #[ORM\ManyToOne]
    private ?Currency $currencyDefault = null;

    #[ORM\ManyToOne]
    private ?Language $languageDefault = null;

    #[ORM\ManyToOne]
    private ?TermOfPayment $termOfPaymentDefault = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccountHolder = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccountBank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccountIban = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccountBic = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankDirectAuthorizationNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $bankDirectAuthorizationDate = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: CustomerInvoiceRecipient::class, mappedBy: 'customer', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $customerInvoiceRecipients;

    #[ORM\OneToMany(targetEntity: CustomerContactPerson::class, mappedBy: 'customer', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $customerContactPersons;

    #[ORM\OneToMany(targetEntity: CustomerNote::class, mappedBy: 'customer', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $customerNotes;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'customers', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'customer', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoices;

    #[ORM\Column(nullable: true)]
    private ?int $ledgerAccountNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialFooterColumn1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialFooterColumn2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialFooterColumn3 = null;

    public function __construct()
    {
        $this->customerInvoiceRecipients = new ArrayCollection();
        $this->customerContactPersons = new ArrayCollection();
        $this->customerNotes = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCustomerType(): ?CustomerType
    {
        return $this->customerType;
    }

    public function setCustomerType(?CustomerType $customerType): static
    {
        $this->customerType = $customerType;

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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): static
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    public function setAddressLine3(?string $addressLine3): static
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    public function getAddressLine4(): ?string
    {
        return $this->addressLine4;
    }

    public function setAddressLine4(?string $addressLine4): static
    {
        $this->addressLine4 = $addressLine4;

        return $this;
    }

    public function getAddressLineCountry(): ?Country
    {
        return $this->addressLineCountry;
    }

    public function setAddressLineCountry(?Country $addressLineCountry): static
    {
        $this->addressLineCountry = $addressLineCountry;

        return $this;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    public function setVatId(?string $vatId): static
    {
        $this->vatId = $vatId;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function isVatExemptInvoicesAllowed(): ?bool
    {
        return $this->vatExemptInvoicesAllowed;
    }

    public function setVatExemptInvoicesAllowed(?bool $vatExemptInvoicesAllowed): static
    {
        $this->vatExemptInvoicesAllowed = $vatExemptInvoicesAllowed;

        return $this;
    }

    public function getAccountingPlanLedgerDefault(): ?AccountingPlanLedger
    {
        return $this->accountingPlanLedgerDefault;
    }

    public function setAccountingPlanLedgerDefault(?AccountingPlanLedger $accountingPlanLedgerDefault): static
    {
        $this->accountingPlanLedgerDefault = $accountingPlanLedgerDefault;

        return $this;
    }

    public function getCurrencyDefault(): ?Currency
    {
        return $this->currencyDefault;
    }

    public function setCurrencyDefault(?Currency $currencyDefault): static
    {
        $this->currencyDefault = $currencyDefault;

        return $this;
    }

    public function getLanguageDefault(): ?Language
    {
        return $this->languageDefault;
    }

    public function setLanguageDefault(?Language $languageDefault): static
    {
        $this->languageDefault = $languageDefault;

        return $this;
    }

    public function getTermOfPaymentDefault(): ?TermOfPayment
    {
        return $this->termOfPaymentDefault;
    }

    public function setTermOfPaymentDefault(?TermOfPayment $termOfPaymentDefault): static
    {
        $this->termOfPaymentDefault = $termOfPaymentDefault;

        return $this;
    }

    public function getBankAccountHolder(): ?string
    {
        return $this->bankAccountHolder;
    }

    public function setBankAccountHolder(?string $bankAccountHolder): static
    {
        $this->bankAccountHolder = $bankAccountHolder;

        return $this;
    }

    public function getBankAccountBank(): ?string
    {
        return $this->bankAccountBank;
    }

    public function setBankAccountBank(?string $bankAccountBank): static
    {
        $this->bankAccountBank = $bankAccountBank;

        return $this;
    }

    public function getBankAccountIban(): ?string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(?string $bankAccountIban): static
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): ?string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(?string $bankAccountBic): static
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getBankDirectAuthorizationNumber(): ?string
    {
        return $this->bankDirectAuthorizationNumber;
    }

    public function setBankDirectAuthorizationNumber(?string $bankDirectAuthorizationNumber): static
    {
        $this->bankDirectAuthorizationNumber = $bankDirectAuthorizationNumber;

        return $this;
    }

    public function getBankDirectAuthorizationDate(): ?DateTimeInterface
    {
        return $this->bankDirectAuthorizationDate;
    }

    public function setBankDirectAuthorizationDate(?DateTimeInterface $bankDirectAuthorizationDate): static
    {
        $this->bankDirectAuthorizationDate = $bankDirectAuthorizationDate;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
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

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return Collection<int, CustomerInvoiceRecipient>
     */
    public function getCustomerInvoiceRecipients(): Collection
    {
        return $this->customerInvoiceRecipients;
    }

    public function addCustomerInvoiceRecipient(CustomerInvoiceRecipient $customerInvoiceRecipient): static
    {
        if (!$this->customerInvoiceRecipients->contains($customerInvoiceRecipient)) {
            $this->customerInvoiceRecipients->add($customerInvoiceRecipient);
            $customerInvoiceRecipient->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerInvoiceRecipient(CustomerInvoiceRecipient $customerInvoiceRecipient): static
    {
        if ($this->customerInvoiceRecipients->removeElement($customerInvoiceRecipient)) {
            // set the owning side to null (unless already changed)
            if ($customerInvoiceRecipient->getCustomer() === $this) {
                $customerInvoiceRecipient->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomerContactPerson>
     */
    public function getCustomerContactPersons(): Collection
    {
        return $this->customerContactPersons;
    }

    public function addCustomerContactPerson(CustomerContactPerson $customerContactPerson): static
    {
        if (!$this->customerContactPersons->contains($customerContactPerson)) {
            $this->customerContactPersons->add($customerContactPerson);
            $customerContactPerson->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerContactPerson(CustomerContactPerson $customerContactPerson): static
    {
        if ($this->customerContactPersons->removeElement($customerContactPerson)) {
            // set the owning side to null (unless already changed)
            if ($customerContactPerson->getCustomer() === $this) {
                $customerContactPerson->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomerNote>
     */
    public function getCustomerNotes(): Collection
    {
        return $this->customerNotes;
    }

    public function addCustomerNote(CustomerNote $customerNote): static
    {
        if (!$this->customerNotes->contains($customerNote)) {
            $this->customerNotes->add($customerNote);
            $customerNote->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerNote(CustomerNote $customerNote): static
    {
        if ($this->customerNotes->removeElement($customerNote)) {
            // set the owning side to null (unless already changed)
            if ($customerNote->getCustomer() === $this) {
                $customerNote->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getLedgerAccountNumber(): ?int
    {
        return $this->ledgerAccountNumber;
    }

    public function setLedgerAccountNumber(?int $ledgerAccountNumber): static
    {
        $this->ledgerAccountNumber = $ledgerAccountNumber;

        return $this;
    }

    public function __toString(): string
    {
        return ($this->getShortName() <> '' ? $this->getShortName() : $this->getName());
    }

    public function getSpecialFooterColumn1(): ?string
    {
        return $this->specialFooterColumn1;
    }

    public function setSpecialFooterColumn1(?string $specialFooterColumn1): static
    {
        $this->specialFooterColumn1 = $specialFooterColumn1;

        return $this;
    }

    public function getSpecialFooterColumn2(): ?string
    {
        return $this->specialFooterColumn2;
    }

    public function setSpecialFooterColumn2(?string $specialFooterColumn2): static
    {
        $this->specialFooterColumn2 = $specialFooterColumn2;

        return $this;
    }

    public function getSpecialFooterColumn3(): ?string
    {
        return $this->specialFooterColumn3;
    }

    public function setSpecialFooterColumn3(?string $specialFooterColumn3): static
    {
        $this->specialFooterColumn3 = $specialFooterColumn3;

        return $this;
    }
}
