<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\InvoicePaymentRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoicePaymentRepository::class)]
class InvoicePayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoicePayments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[ORM\ManyToOne]
    private ?AccountingPlanLedger $accountingPlanLedger = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
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

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountNice(bool $includeCurrencyAlpha3 = false): ?string
    {
        if(!$this->getAmount())
            return null;

        $preCurrency = '';
        $postCurrency = '';

        $decimalPoint = ($this->getCurrency()->getAlpha3() == 'CHF' ? '.' : ',');
        $thousandsSeparator = ($this->getCurrency()->getAlpha3() == 'CHF' ? ',' : '.');

        if($this->getCurrency()->getAlpha3() == 'CHF' && $includeCurrencyAlpha3)
                $preCurrency = $this->getCurrency()->getAlpha3().' ';
        elseif($includeCurrencyAlpha3)
                $postCurrency = ' '.$this->getCurrency()->getAlpha3();

        return $preCurrency.number_format($this->getAmount(), 2, $decimalPoint, $thousandsSeparator).$postCurrency;
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
}
