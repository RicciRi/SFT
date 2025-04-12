<?php

namespace App\Entity;

use App\Repository\FileDownloadLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileDownloadLogRepository::class)]
class FileDownloadLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fileDownloadLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TransferredFile $file = null;

    #[ORM\ManyToOne(inversedBy: 'fileDownloadLogs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $downloadedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $downloadedAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?TransferredFile
    {
        return $this->file;
    }

    public function setFile(?TransferredFile $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getDownloadedBy(): ?User
    {
        return $this->downloadedBy;
    }

    public function setDownloadedBy(?User $downloadedBy): static
    {
        $this->downloadedBy = $downloadedBy;

        return $this;
    }

    public function getDownloadedAt(): ?\DateTimeImmutable
    {
        return $this->downloadedAt;
    }

    public function setDownloadedAt(\DateTimeImmutable $downloadedAt): static
    {
        $this->downloadedAt = $downloadedAt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }
}
