<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\PrincipalRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrincipalRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Principal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vatId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $vatExempt = null;

    #[ORM\ManyToOne]
    private ?CompanyType $vatCompanyType = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $vatReportCalculation = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $vatReportInterval = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn3 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn1En = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn2En = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerColumn3En = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fibuRecipientEmail1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fibuRecipientEmail2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fibuRecipientEmail3 = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $fibuDocumentNumberRange = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $capserInvoiceAddress = null;

    #[ORM\ManyToOne]
    private ?CapserPackage $capserPackage = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $mainContact = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'principals')]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: TermOfPayment::class, mappedBy: 'principal', orphanRemoval: true)]
    private Collection $termOfPayments;

    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'principal')]
    private Collection $tags;

    #[ORM\OneToOne(inversedBy: 'principal', cascade: ['persist', 'remove'])]
    private ?AccountingPlan $accountingPlan = null;

    #[ORM\OneToMany(targetEntity: Customer::class, mappedBy: 'principal', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $customers;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'principal', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoices;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->termOfPayments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): static
    {
        $this->fax = $fax;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

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

    public function isVatExempt(): ?bool
    {
        return $this->vatExempt;
    }

    public function setVatExempt(?bool $vatExempt): static
    {
        $this->vatExempt = $vatExempt;

        return $this;
    }

    public function getVatCompanyType(): ?CompanyType
    {
        return $this->vatCompanyType;
    }

    public function setVatCompanyType(?CompanyType $vatCompanyType): static
    {
        $this->vatCompanyType = $vatCompanyType;

        return $this;
    }

    public function getVatReportCalculation(): ?string
    {
        return $this->vatReportCalculation;
    }

    public function setVatReportCalculation(?string $vatReportCalculation): static
    {
        $this->vatReportCalculation = $vatReportCalculation;

        return $this;
    }

    public function getVatReportInterval(): ?string
    {
        return $this->vatReportInterval;
    }

    public function setVatReportInterval(?string $vatReportInterval): static
    {
        $this->vatReportInterval = $vatReportInterval;

        return $this;
    }

    public function getFooterColumn1(): ?string
    {
        return $this->footerColumn1;
    }

    public function setFooterColumn1(?string $footerColumn1): static
    {
        $this->footerColumn1 = $footerColumn1;

        return $this;
    }

    public function getfooterColumn2(): ?string
    {
        return $this->footerColumn2;
    }

    public function setfooterColumn2(?string $footerColumn2): static
    {
        $this->footerColumn2 = $footerColumn2;

        return $this;
    }

    public function getfooterColumn3(): ?string
    {
        return $this->footerColumn3;
    }

    public function setfooterColumn3(?string $footerColumn3): static
    {
        $this->footerColumn3 = $footerColumn3;

        return $this;
    }

    public function getFooterColumn1En(): ?string
    {
        return $this->footerColumn1En;
    }

    public function setFooterColumn1En(?string $footerColumn1En): static
    {
        $this->footerColumn1En = $footerColumn1En;

        return $this;
    }

    public function getFooterColumn2En(): ?string
    {
        return $this->footerColumn2En;
    }

    public function setFooterColumn2En(?string $footerColumn2En): static
    {
        $this->footerColumn2En = $footerColumn2En;

        return $this;
    }

    public function getFooterColumn3En(): ?string
    {
        return $this->footerColumn3En;
    }

    public function setFooterColumn3En(?string $footerColumn3En): static
    {
        $this->footerColumn3En = $footerColumn3En;

        return $this;
    }

    public function getFibuRecipientEmail1(): ?string
    {
        return $this->fibuRecipientEmail1;
    }

    public function setFibuRecipientEmail1(?string $fibuRecipientEmail1): static
    {
        $this->fibuRecipientEmail1 = $fibuRecipientEmail1;

        return $this;
    }

    public function getFibuRecipientEmail2(): ?string
    {
        return $this->fibuRecipientEmail2;
    }

    public function setFibuRecipientEmail2(?string $fibuRecipientEmail2): static
    {
        $this->fibuRecipientEmail2 = $fibuRecipientEmail2;

        return $this;
    }

    public function getFibuRecipientEmail3(): ?string
    {
        return $this->fibuRecipientEmail3;
    }

    public function setFibuRecipientEmail3(?string $fibuRecipientEmail3): static
    {
        $this->fibuRecipientEmail3 = $fibuRecipientEmail3;

        return $this;
    }

    public function getFibuDocumentNumberRange(): ?string
    {
        return $this->fibuDocumentNumberRange;
    }

    public function setFibuDocumentNumberRange(?string $fibuDocumentNumberRange): static
    {
        $this->fibuDocumentNumberRange = $fibuDocumentNumberRange;

        return $this;
    }

    public function getCapserInvoiceAddress(): ?string
    {
        return $this->capserInvoiceAddress;
    }

    public function setCapserInvoiceAddress(?string $capserInvoiceAddress): static
    {
        $this->capserInvoiceAddress = $capserInvoiceAddress;

        return $this;
    }

    public function getCapserPackage(): ?CapserPackage
    {
        return $this->capserPackage;
    }

    public function setCapserPackage(?CapserPackage $capserPackage): static
    {
        $this->capserPackage = $capserPackage;

        return $this;
    }

    public function getMainContact(): ?User
    {
        return $this->mainContact;
    }

    public function setMainContact(?User $mainContact): static
    {
        $this->mainContact = $mainContact;

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

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addPrincipal($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removePrincipal($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TermOfPayment>
     */
    public function getTermOfPayments(): Collection
    {
        return $this->termOfPayments;
    }

    public function addTermOfPayment(TermOfPayment $termOfPayment): static
    {
        if (!$this->termOfPayments->contains($termOfPayment)) {
            $this->termOfPayments->add($termOfPayment);
            $termOfPayment->setPrincipal($this);
        }

        return $this;
    }

    public function removeTermOfPayment(TermOfPayment $termOfPayment): static
    {
        if ($this->termOfPayments->removeElement($termOfPayment)) {
            // set the owning side to null (unless already changed)
            if ($termOfPayment->getPrincipal() === $this) {
                $termOfPayment->setPrincipal(null);
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
            $tag->setPrincipal($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getPrincipal() === $this) {
                $tag->setPrincipal(null);
            }
        }

        return $this;
    }

    public function getAccountingPlan(): ?AccountingPlan
    {
        return $this->accountingPlan;
    }

    public function setAccountingPlan(?AccountingPlan $accountingPlan): static
    {
        $this->accountingPlan = $accountingPlan;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setPrincipal($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getPrincipal() === $this) {
                $customer->setPrincipal(null);
            }
        }

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
            $invoice->setPrincipal($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getPrincipal() === $this) {
                $invoice->setPrincipal(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return ($this->getShortName() <> '' ? $this->getShortName() : $this->getName());
    }
}
