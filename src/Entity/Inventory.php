<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ORM\Table(name: 'inventory')]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $itemName = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\Column(options: ['default' => 5])]
    private int $lowStockThreshold = 5;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUpdated = null;

    public function __construct()
    {
        $this->lastUpdated = new \DateTimeImmutable();
        $this->quantity = 0;
        $this->lowStockThreshold = 5;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function setItemName(string $itemName): static
    {
        $this->itemName = $itemName;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->lastUpdated = new \DateTimeImmutable();

        return $this;
    }

    public function getLowStockThreshold(): int
    {
        return $this->lowStockThreshold;
    }

    public function setLowStockThreshold(int $lowStockThreshold): static
    {
        $this->lowStockThreshold = $lowStockThreshold;
        $this->lastUpdated = new \DateTimeImmutable();

        return $this;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->lowStockThreshold;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }
}

