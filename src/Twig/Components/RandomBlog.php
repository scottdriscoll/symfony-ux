<?php

namespace App\Twig\Components;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent]
final class RandomBlog extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly BlogPostRepository $repository
    ) { }

    #[LiveProp]
    public ?BlogPost $post = null;

    #[LiveProp(writable: true, onUpdated: 'loadCurrentBlog')]
    public ?int $blogId = null;

    #[LiveListener('getRandomBlog')]
    public function getRandomBlogPost(): void
    {
       $this->post = $this->repository->findRandom();
    }

    public function loadCurrentBlog(): void
    {
        if ($this->blogId) {
            $this->post = $this->repository->find($this->blogId);
        }
    }
}
