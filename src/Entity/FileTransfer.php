<?php

namespace App\Entity;

use App\Enum\TransferStatus;
use App\Repository\FileTransferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileTransferRepository::class)]
class FileTransfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fileTransfers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $recipientEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expirationAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, enumType: TransferStatus::class)]
    private ?TransferStatus $status = TransferStatus::UPLOADED;

    /**
     * @var Collection<int, TransferredFile>
     */
    #[ORM\OneToMany(targetEntity: TransferredFile::class, mappedBy: 'fileTransfer', cascade: ['persist', 'remove'])]
    private Collection $transferredFiles;

    #[ORM\ManyToOne(inversedBy: 'fileTransfers')]
    private ?Company $company = null;

    public function __construct()
    {
        $this->transferredFiles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->uuid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(string $recipientEmail): static
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getExpirationAt(): ?\DateTimeImmutable
    {
        return $this->expirationAt;
    }

    public function setExpirationAt(\DateTimeImmutable $expirationAt): static
    {
        $this->expirationAt = $expirationAt;

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

    public function getStatus(): ?TransferStatus
    {
        return $this->status;
    }

    public function setStatus(TransferStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function markAsDownloaded(): self
    {
        $this->status = TransferStatus::DOWNLOADED;

        return $this;
    }

    /**
     * @return Collection<int, TransferredFile>
     */
    public function getTransferredFiles(): Collection
    {
        return $this->transferredFiles;
    }

    public function addTransferredFile(TransferredFile $transferredFile): static
    {
        if (!$this->transferredFiles->contains($transferredFile)) {
            $this->transferredFiles->add($transferredFile);
            $transferredFile->setFileTransfer($this);
        }

        return $this;
    }

    public function removeTransferredFile(TransferredFile $transferredFile): static
    {
        if ($this->transferredFiles->removeElement($transferredFile)) {
            if ($transferredFile->getFileTransfer() === $this) {
                $transferredFile->setFileTransfer(null);
            }
        }

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}
