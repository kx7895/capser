<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\AccountingPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountingPlanRepository::class)]
class AccountingPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'accountingPlan', cascade: ['persist', 'remove'])]
    private ?Principal $principal = null;

    #[ORM\OneToMany(targetEntity: AccountingPlanGroup::class, mappedBy: 'accountingPlan', orphanRemoval: true)]
    private Collection $accountingPlanGroups;

    public function __construct()
    {
        $this->accountingPlanGroups = new ArrayCollection();
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

    public function getPrincipal(): ?Principal
    {
        return $this->principal;
    }

    public function setPrincipal(?Principal $principal): static
    {
        // unset the owning side of the relation if necessary
        if ($principal === null && $this->principal !== null) {
            $this->principal->setAccountingPlan(null);
        }

        // set the owning side of the relation if necessary
        if ($principal !== null && $principal->getAccountingPlan() !== $this) {
            $principal->setAccountingPlan($this);
        }

        $this->principal = $principal;

        return $this;
    }

    /**
     * @return Collection<int, AccountingPlanGroup>
     */
    public function getAccountingPlanGroups(): Collection
    {
        return $this->accountingPlanGroups;
    }

    public function addAccountingPlanGroup(AccountingPlanGroup $accountingPlanGroup): static
    {
        if (!$this->accountingPlanGroups->contains($accountingPlanGroup)) {
            $this->accountingPlanGroups->add($accountingPlanGroup);
            $accountingPlanGroup->setAccountingPlan($this);
        }

        return $this;
    }

    public function removeAccountingPlanGroup(AccountingPlanGroup $accountingPlanGroup): static
    {
        if ($this->accountingPlanGroups->removeElement($accountingPlanGroup)) {
            // set the owning side to null (unless already changed)
            if ($accountingPlanGroup->getAccountingPlan() === $this) {
                $accountingPlanGroup->setAccountingPlan(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
