<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 5,
        max: 200,
        minMessage: 'Le comment doit faire au moins 5 caractères',
        maxMessage: 'Le comment doit faire au maximum 200 caractères',
    )]
    private ?string $Comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $public_date = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Article $article = null;

    public function __construct()
    {
        $this->public_date = new DateTime();
        $this->status = true;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(string $Comment): self
    {
        $this->Comment = $Comment;

        return $this;
    }

    public function getPublicDate(): ?\DateTimeInterface
    {
        return $this->public_date;
    }

    public function setPublicDate(\DateTimeInterface $public_date): self
    {
        $this->public_date = $public_date;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
