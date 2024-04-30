<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\AccountingPlanLedgerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountingPlanLedgerRepository::class)]
class AccountingPlanLedger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accountingPlanLedgers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccountingPlanGroup $accountingPlanGroup = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'accountingPlanLedger')]
    private Collection $invoices;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountingPlanGroup(): ?AccountingPlanGroup
    {
        return $this->accountingPlanGroup;
    }

    public function setAccountingPlanGroup(?AccountingPlanGroup $accountingPlanGroup): static
    {
        $this->accountingPlanGroup = $accountingPlanGroup;

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
            $invoice->setAccountingPlanLedger($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getAccountingPlanLedger() === $this) {
                $invoice->setAccountingPlanLedger(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
