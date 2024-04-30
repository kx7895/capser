<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\AccountingPlanGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountingPlanGroupRepository::class)]
class AccountingPlanGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accountingPlanGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccountingPlan $accountingPlan = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childAccountingPlanGroups')]
    private ?self $parentAccountingPlanGroup = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentAccountingPlanGroup')]
    private Collection $childAccountingPlanGroups;

    #[ORM\OneToMany(targetEntity: AccountingPlanLedger::class, mappedBy: 'accountingPlanGroup', orphanRemoval: true)]
    private Collection $accountingPlanLedgers;

    public function __construct()
    {
        $this->childAccountingPlanGroups = new ArrayCollection();
        $this->accountingPlanLedgers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParentAccountingPlanGroup(): ?self
    {
        return $this->parentAccountingPlanGroup;
    }

    public function setParentAccountingPlanGroup(?self $parentAccountingPlanGroup): static
    {
        $this->parentAccountingPlanGroup = $parentAccountingPlanGroup;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildAccountingPlanGroups(): Collection
    {
        return $this->childAccountingPlanGroups;
    }

    public function addChildAccountingPlanGroup(self $childAccountingPlanGroup): static
    {
        if (!$this->childAccountingPlanGroups->contains($childAccountingPlanGroup)) {
            $this->childAccountingPlanGroups->add($childAccountingPlanGroup);
            $childAccountingPlanGroup->setParentAccountingPlanGroup($this);
        }

        return $this;
    }

    public function removeChildAccountingPlanGroup(self $childAccountingPlanGroup): static
    {
        if ($this->childAccountingPlanGroups->removeElement($childAccountingPlanGroup)) {
            // set the owning side to null (unless already changed)
            if ($childAccountingPlanGroup->getParentAccountingPlanGroup() === $this) {
                $childAccountingPlanGroup->setParentAccountingPlanGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccountingPlanLedger>
     */
    public function getAccountingPlanLedgers(): Collection
    {
        return $this->accountingPlanLedgers;
    }

    public function addAccountingPlanLedger(AccountingPlanLedger $accountingPlanLedger): static
    {
        if (!$this->accountingPlanLedgers->contains($accountingPlanLedger)) {
            $this->accountingPlanLedgers->add($accountingPlanLedger);
            $accountingPlanLedger->setAccountingPlanGroup($this);
        }

        return $this;
    }

    public function removeAccountingPlanLedger(AccountingPlanLedger $accountingPlanLedger): static
    {
        if ($this->accountingPlanLedgers->removeElement($accountingPlanLedger)) {
            // set the owning side to null (unless already changed)
            if ($accountingPlanLedger->getAccountingPlanGroup() === $this) {
                $accountingPlanLedger->setAccountingPlanGroup(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
