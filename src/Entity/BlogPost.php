<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost
{
    const STATE_DRAFT = 'draft';
    const STATE_REVIEWED = 'reviewed';
    const STATE_REJECTED = 'rejected';
    const STATE_PUBLISHED = 'published';

    const TRANSITION_TO_REVIEW = 'to_review';
    const TRANSITION_PUBLISH = 'publish';
    const TRANSITION_REJECT = 'reject';
    const TRANSITION_TO_DRAFT = 'to_draft';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currentPlace = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }


    public function setCurrentPlace(string $currentPlace, array $context = []): static
    {
        $this->currentPlace = $currentPlace;

        return $this;
    }
}
