<?php

namespace App\Entity;

use App\Enum\FileStatus;
use App\Repository\TransferredFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferredFileRepository::class)]
class TransferredFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transferredFiles')]
    private ?FileTransfer $fileTransfer = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $storedFilename = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, FileDownloadLog>
     */
    #[ORM\OneToMany(targetEntity: FileDownloadLog::class, mappedBy: 'file')]
    private Collection $fileDownloadLogs;

    #[ORM\Column(length: 255, enumType: FileStatus::class)]
    private ?FileStatus $status = FileStatus::UPLOADED;

    public function __construct()
    {
        $this->fileDownloadLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileTransfer(): ?FileTransfer
    {
        return $this->fileTransfer;
    }

    public function setFileTransfer(?FileTransfer $fileTransfer): static
    {
        $this->fileTransfer = $fileTransfer;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, FileDownloadLog>
     */
    public function getFileDownloadLogs(): Collection
    {
        return $this->fileDownloadLogs;
    }

    public function addFileDownloadLog(FileDownloadLog $fileDownloadLog): static
    {
        if (!$this->fileDownloadLogs->contains($fileDownloadLog)) {
            $this->fileDownloadLogs->add($fileDownloadLog);
            $fileDownloadLog->setFile($this);
        }

        return $this;
    }

    public function removeFileDownloadLog(FileDownloadLog $fileDownloadLog): static
    {
        if ($this->fileDownloadLogs->removeElement($fileDownloadLog)) {
            // set the owning side to null (unless already changed)
            if ($fileDownloadLog->getFile() === $this) {
                $fileDownloadLog->setFile(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?FileStatus
    {
        return $this->status;
    }

    public function setStatus(FileStatus $status): static
    {
        $this->status = $status;

        return $this;
    }


    public function markAsDownloaded(): static
    {
        $this->status = FileStatus::DOWNLOADED;

        return $this;
    }

    public function markAsExpired(): static
    {
        $this->status = FileStatus::EXPIRED;

        return $this;
    }
}
