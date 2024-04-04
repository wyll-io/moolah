<?php

namespace App\Entity;

use App\Repository\BillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: BillRepository::class)]
#[ORM\Table(name: "bill")]
class Bill
{
    public const BILL_TYPE_EXPENSE = 1;
    public const BILL_TYPE_REFUND = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $name;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private $content;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private $date;

    #[ORM\Column(type: Types::INTEGER)]
    private $billType;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private $created_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private $updated_at;

    #[ORM\ManyToOne(inversedBy: 'bills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $payer = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'payerBills')]
    private Collection $participants;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->participants = new ArrayCollection();
        $this->billType = 1;
    }
    public function getId() : ?int
    {
        return $this->id;
    }
   
    public function getName() : ?string
    {
        return $this->name;
    }
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    public function getContent() : ?string
    {
        return $this->content;
    }
    public function setContent(string $content) : self
    {
        $this->content = $content;
        return $this;
    }
    public function getBillType() : int
    {
        return $this->billType;
    }
    public function setBillType(int $billType) : self
    {
        $this->billType = $billType;
        return $this;
    }
    public function getDate() : \DateTimeInterface
    {
        return $this->date;
    }
    public function setDate(\DateTimeInterface $date) : self
    {
        $this->date = $date;
        return $this;
    }
    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->created_at;
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt) : self
    {
        $this->created_at = $createdAt;
        return $this;
    }
    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updated_at;
    }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt) : self
    {
        $this->updated_at = $updatedAt;
        return $this;
    }
    public function getPayer(): ?User
    {
        return $this->payer;
    }
    public function setPayer(?User $payer): static
    {
        $this->payer = $payer;

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

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }
}