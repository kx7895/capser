<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoiceRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    public const VATRATES = [
        '19,00 %' => 19, // DE
        '8,10 %' => 8.1, // CH
        '7,00 %' => 7, // DE
        '5,00 %' => 5, // UAE
        '2,60 %' => 2.6, // CH
        '0,00 %' => 0 // allgemein
    ];

    public const VATTYPES = [
        'Reguläre MwSt.' => 'REG', // Regular
        'Reverse Charge' => 'RC', // Reverse Charge
        'Keine MwSt.' => 'NOT' // No VAT
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceAttachment::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoiceAttachments;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoicePosition::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoicePositions;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceMailing::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoiceMailings;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceNote::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoiceNotes;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoicePayment::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $invoicePayments;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'invoices')]
    private Collection $tags;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $invoiceReference = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Principal $principal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?InvoiceType $invoiceType = null;

    #[ORM\Column(nullable: true)]
    private ?int $number = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $periodFrom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $periodTo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $due = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $introText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $outroText = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?AccountingPlanLedger $accountingPlanLedger = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?TermOfPayment $termOfPayment = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $vatType = null;

    #[ORM\Column]
    private ?float $vatRate = null;

    #[ORM\Column(nullable: true)]
    private ?float $amountNet = null;

    #[ORM\Column(nullable: true)]
    private ?float $amountGross = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $costcenterExternal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceExternal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sent = null;

    #[ORM\Column(nullable: true)]
    private ?bool $reminded = null;

    #[ORM\Column(nullable: true)]
    private ?bool $cancelled = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $niceFilename = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $storageFilename = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    #[ORM\Column(length: 255)]
    private ?string $hCustomerName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hCustomerShortName = null;

    #[ORM\Column(length: 255)]
    private ?string $hPrincipalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hPrincipalShortName = null;

    #[ORM\Column(nullable: true)]
    private ?bool $paid = null;

    public function __construct()
    {
        $this->invoiceAttachments = new ArrayCollection();
        $this->invoicePositions = new ArrayCollection();
        $this->invoiceMailings = new ArrayCollection();
        $this->invoiceNotes = new ArrayCollection();
        $this->invoicePayments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, InvoiceAttachment>
     */
    public function getInvoiceAttachments(): Collection
    {
        return $this->invoiceAttachments;
    }

    public function addInvoiceAttachment(InvoiceAttachment $invoiceAttachment): static
    {
        if (!$this->invoiceAttachments->contains($invoiceAttachment)) {
            $this->invoiceAttachments->add($invoiceAttachment);
            $invoiceAttachment->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceAttachment(InvoiceAttachment $invoiceAttachment): static
    {
        if ($this->invoiceAttachments->removeElement($invoiceAttachment)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAttachment->getInvoice() === $this) {
                $invoiceAttachment->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoicePosition>
     */
    public function getInvoicePositions(): Collection
    {
        return $this->invoicePositions;
    }

    public function addInvoicePosition(InvoicePosition $invoicePosition): static
    {
        if (!$this->invoicePositions->contains($invoicePosition)) {
            $this->invoicePositions->add($invoicePosition);
            $invoicePosition->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoicePosition(InvoicePosition $invoicePosition): static
    {
        if ($this->invoicePositions->removeElement($invoicePosition)) {
            // set the owning side to null (unless already changed)
            if ($invoicePosition->getInvoice() === $this) {
                $invoicePosition->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceMailing>
     */
    public function getInvoiceMailings(): Collection
    {
        return $this->invoiceMailings;
    }

    public function addInvoiceMailing(InvoiceMailing $invoiceMailing): static
    {
        if (!$this->invoiceMailings->contains($invoiceMailing)) {
            $this->invoiceMailings->add($invoiceMailing);
            $invoiceMailing->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceMailing(InvoiceMailing $invoiceMailing): static
    {
        if ($this->invoiceMailings->removeElement($invoiceMailing)) {
            // set the owning side to null (unless already changed)
            if ($invoiceMailing->getInvoice() === $this) {
                $invoiceMailing->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceNote>
     */
    public function getInvoiceNotes(): Collection
    {
        return $this->invoiceNotes;
    }

    public function addInvoiceNote(InvoiceNote $invoiceNote): static
    {
        if (!$this->invoiceNotes->contains($invoiceNote)) {
            $this->invoiceNotes->add($invoiceNote);
            $invoiceNote->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceNote(InvoiceNote $invoiceNote): static
    {
        if ($this->invoiceNotes->removeElement($invoiceNote)) {
            // set the owning side to null (unless already changed)
            if ($invoiceNote->getInvoice() === $this) {
                $invoiceNote->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoicePayment>
     */
    public function getInvoicePayments(): Collection
    {
        return $this->invoicePayments;
    }

    public function addInvoicePayment(InvoicePayment $invoicePayment): static
    {
        if (!$this->invoicePayments->contains($invoicePayment)) {
            $this->invoicePayments->add($invoicePayment);
            $invoicePayment->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoicePayment(InvoicePayment $invoicePayment): static
    {
        if ($this->invoicePayments->removeElement($invoicePayment)) {
            // set the owning side to null (unless already changed)
            if ($invoicePayment->getInvoice() === $this) {
                $invoicePayment->setInvoice(null);
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

    public function getInvoiceReference(): ?self
    {
        return $this->invoiceReference;
    }

    public function setInvoiceReference(?self $invoiceReference): static
    {
        $this->invoiceReference = $invoiceReference;

        return $this;
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

    public function getCustomerName(): ?string
    {
        return $this->getCustomer()?->getName();
    }

    public function getCustomerShortName(): ?string
    {
        return $this->getCustomer()?->getShortName();
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

    public function getPrincipalName(): ?string
    {
        return $this->getPrincipal()?->getName();
    }

    public function getPrincipalShortName(): ?string
    {
        return $this->getPrincipal()?->getShortName();
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

    public function getInvoiceType(): ?InvoiceType
    {
        return $this->invoiceType;
    }

    public function setInvoiceType(?InvoiceType $invoiceType): static
    {
        $this->invoiceType = $invoiceType;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getPeriodFrom(): ?DateTimeInterface
    {
        return $this->periodFrom;
    }

    public function setPeriodFrom(?DateTimeInterface $periodFrom): static
    {
        $this->periodFrom = $periodFrom;

        return $this;
    }

    public function getPeriodTo(): ?DateTimeInterface
    {
        return $this->periodTo;
    }

    public function setPeriodTo(?DateTimeInterface $periodTo): static
    {
        $this->periodTo = $periodTo;

        return $this;
    }

    public function getDue(): ?DateTimeInterface
    {
        return $this->due;
    }

    public function setDue(DateTimeInterface $due): static
    {
        $this->due = $due;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getIntroText(): ?string
    {
        return $this->introText;
    }

    public function setIntroText(?string $introText): static
    {
        $this->introText = $introText;

        return $this;
    }

    public function getOutroText(): ?string
    {
        return $this->outroText;
    }

    public function setOutroText(?string $outroText): static
    {
        $this->outroText = $outroText;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAccountingPlanLedger(): ?AccountingPlanLedger
    {
        return $this->accountingPlanLedger;
    }

    public function setAccountingPlanLedger(?AccountingPlanLedger $accountingPlanLedger): static
    {
        $this->accountingPlanLedger = $accountingPlanLedger;

        return $this;
    }

    public function getTermOfPayment(): ?TermOfPayment
    {
        return $this->termOfPayment;
    }

    public function setTermOfPayment(?TermOfPayment $termOfPayment): static
    {
        $this->termOfPayment = $termOfPayment;

        return $this;
    }

    public function getVatType(): ?string
    {
        return $this->vatType;
    }

    public function setVatType(?string $vatType): static
    {
        $this->vatType = $vatType;

        return $this;
    }

    public function getVatRate(): ?float
    {
        return $this->vatRate;
    }

    public function setVatRate(float $vatRate): static
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    public function getAmountNet(): ?float
    {
        return $this->amountNet;
    }

    public function setAmountNet(?float $amountNet): static
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountGross(): ?float
    {
        return $this->amountGross;
    }

    public function setAmountGross(?float $amountGross): static
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    private function getAmountNice(?float $amount, bool $includeCurrencyAlpha3): ?string
    {
        if(!$amount)
            return null;

        $preCurrency = '';
        $postCurrency = '';

        $decimalPoint = ($this->getCurrency()->getAlpha3() == 'CHF' ? '.' : ',');
        $thousandsSeparator = ($this->getCurrency()->getAlpha3() == 'CHF' ? ',' : '.');

        if($this->getCurrency()->getAlpha3() == 'CHF' && $includeCurrencyAlpha3)
            $preCurrency = $this->getCurrency()->getAlpha3().' ';
        elseif($includeCurrencyAlpha3)
            $postCurrency = ' '.$this->getCurrency()->getAlpha3();

        return $preCurrency.number_format($amount, 2, $decimalPoint, $thousandsSeparator).$postCurrency;
    }

    public function getAmountNetNice($includeCurrencyAlpha3 = false): ?string
    {
        return $this->getAmountNice($this->getAmountNet(), $includeCurrencyAlpha3);
    }

    public function getAmountGrossNice($includeCurrencyAlpha3 = false): ?string
    {
        return $this->getAmountNice($this->getAmountGross(), $includeCurrencyAlpha3);
    }

    public function getCostcenterExternal(): ?string
    {
        return $this->costcenterExternal;
    }

    public function setCostcenterExternal(?string $costcenterExternal): static
    {
        $this->costcenterExternal = $costcenterExternal;

        return $this;
    }

    public function getReferenceExternal(): ?string
    {
        return $this->referenceExternal;
    }

    public function setReferenceExternal(?string $referenceExternal): static
    {
        $this->referenceExternal = $referenceExternal;

        return $this;
    }

    public function isSent(): ?bool
    {
        return $this->sent;
    }

    public function setSent(?bool $sent): static
    {
        $this->sent = $sent;

        return $this;
    }

    public function getSentLabel(): string
    {
        if($this->isSent())
            return '<span class="badge text-bg-success-soft">Verschickt</span>';
        else
            return '<span class="badge text-bg-light">Offen</span>';
    }

    public function isReminded(): ?bool
    {
        return $this->reminded;
    }

    public function setReminded(?bool $reminded): static
    {
        $this->reminded = $reminded;

        return $this;
    }

    public function getRemindedLabel(): string
    {
        if($this->isReminded())
            return '<span class="badge text-bg-success-soft">Verschickt</span>';
        else
            return '-';
    }

    public function isCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(?bool $cancelled): static
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    public function getCancelledLabel(): string
    {
        if($this->isCancelled())
            return '<span class="badge text-bg-info-soft">Storniert</span>';
        else
            return '';
    }

    public function getNiceFilename(): ?string
    {
        return $this->niceFilename;
    }

    public function setNiceFilename(?string $niceFilename): static
    {
        $this->niceFilename = $niceFilename;

        return $this;
    }

    public function getStorageFilename(): ?string
    {
        return $this->storageFilename;
    }

    public function setStorageFilename(?string $storageFilename): static
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

    public function getHCustomerName(): ?string
    {
        return $this->hCustomerName;
    }

    public function setHCustomerName(string $hCustomerName): static
    {
        $this->hCustomerName = $hCustomerName;

        return $this;
    }

    public function getHCustomerShortName(): ?string
    {
        return $this->hCustomerShortName;
    }

    public function setHCustomerShortName(?string $hCustomerShortName): static
    {
        $this->hCustomerShortName = $hCustomerShortName;

        return $this;
    }

    public function getHPrincipalName(): ?string
    {
        return $this->hPrincipalName;
    }

    public function setHPrincipalName(string $hPrincipalName): static
    {
        $this->hPrincipalName = $hPrincipalName;

        return $this;
    }

    public function getHPrincipalShortName(): ?string
    {
        return $this->hPrincipalShortName;
    }

    public function setHPrincipalShortName(?string $hPrincipalShortName): static
    {
        $this->hPrincipalShortName = $hPrincipalShortName;

        return $this;
    }

    public function isInvoice(): bool
    {
        return in_array($this->getInvoiceType()->getType(), ['IN', 'RE', 'RG']);
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(?bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaymentLabel(): string
    {
        if($this->getPaymentStatus() == 'paid')
            return '<span class="badge text-bg-success-soft">Bezahlt</span>';
        elseif($this->getPaymentStatus() == 'partly')
            return '<span class="badge text-bg-warning-soft">Teilw. bezahlt</span>';
        elseif($this->getPaymentStatus() == 'overdue')
            return '<span class="badge text-bg-danger-soft">Überfällig</span>';
        else
            return '<span class="badge text-bg-light">Offen</span>';
    }

    public function getPaymentStatus(): string
    {
        if($this->isPaid())
            return 'paid';
        elseif(!$this->getInvoicePayments()->isEmpty())
            return 'partly';
        elseif($this->getDue()->format('Ymd') < (new DateTime())->format('Ymd'))
            return 'overdue';
        else
            return 'due';
    }

    public function getAmountDue(): float
    {
        if($this->isPaid()) {
            return 0;
        } elseif(!$this->getInvoicePayments()->isEmpty()) {
            if($this->isInvoice()) {
                $reduce = 0;
                foreach($this->getInvoicePayments() as $invoicePayment) {
                    if($invoicePayment->getCurrency() === $this->getCurrency())
                        $reduce += $invoicePayment->getAmount();
                }
                return ($this->getAmountGross())-$reduce;
            } else {
                $add = 0;
                foreach($this->getInvoicePayments() as $invoicePayment) {
                    if($invoicePayment->getCurrency() === $this->getCurrency())
                        $add += $invoicePayment->getAmount();
                }
                return ($this->getAmountGross())+$add;
            }
        } else {
            return $this->getAmountGross();
        }
    }

    public function getAmountDueNice($includeCurrencyAlpha3 = false): ?string
    {
        return $this->getAmountNice($this->getAmountDue(), $includeCurrencyAlpha3);
    }

    public function __toString(): string
    {
        return $this->getInvoiceType()->getType().' '.($this->getNumber() <> '' ? $this->getNumber() : 'PREVIEW');
    }
}
