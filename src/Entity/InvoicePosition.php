<?php /** @noinspection PhpUnused */

namespace App\Entity;

//use App\Repository\InvoicePositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

//#[ORM\Entity(repositoryClass: InvoicePositionRepository::class)]
#[ORM\Entity(repositoryClass: SortableRepository::class)]
class InvoicePosition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoicePositions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\SortableGroup]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $discount = null;

    #[ORM\Column(nullable: true)]
    private ?float $taxRate = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne]
    private ?Unit $unit = null;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceNice(bool $includeCurrencyAlpha3 = false): ?string
    {
        if(!$this->getPrice())
            return null;

        $preCurrency = '';
        $postCurrency = '';

        $decimalPoint = ($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' ? '.' : ',');
        $thousandsSeparator = ($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' ? ',' : '.');

        if($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' && $includeCurrencyAlpha3)
            $preCurrency = $this->getInvoice()->getCurrency()->getAlpha3().' ';
        elseif($includeCurrencyAlpha3)
            $postCurrency = ' '.$this->getInvoice()->getCurrency()->getAlpha3();

        return $preCurrency.number_format($this->getPrice(), 2, $decimalPoint, $thousandsSeparator).$postCurrency;
    }

    public function getSumNice(bool $includeCurrencyAlpha3 = false): ?string
    {
        if(!$this->getPrice() || !$this->getAmount())
            return null;

        $preCurrency = '';
        $postCurrency = '';

        $decimalPoint = ($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' ? '.' : ',');
        $thousandsSeparator = ($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' ? ',' : '.');

        if($this->getInvoice()->getCurrency()->getAlpha3() == 'CHF' && $includeCurrencyAlpha3)
            $preCurrency = $this->getInvoice()->getCurrency()->getAlpha3().' ';
        elseif($includeCurrencyAlpha3)
            $postCurrency = ' '.$this->getInvoice()->getCurrency()->getAlpha3();

        return $preCurrency.number_format(($this->getPrice()*$this->getAmount()), 2, $decimalPoint, $thousandsSeparator).$postCurrency;
    }


    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->taxRate;
    }

    public function setTaxRate(?float $taxRate): static
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getText();
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }
}
