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

    #[LiveProp]
    public ?BlogPost $post = null;

    #[LiveListener('getRandomBlog')]
    public function getRandomBlogPost(BlogPostRepository $repository): void
    {
       $this->post = $repository->findRandom();
    }
}
