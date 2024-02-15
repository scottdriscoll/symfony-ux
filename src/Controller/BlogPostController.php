<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/blog/post')]
class BlogPostController extends AbstractController
{
    #[Route('/', name: 'app_blog_post_index', methods: ['GET'])]
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        return $this->render('blog_post/index.html.twig', [
            'blog_posts' => $blogPostRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_blog_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blogPost);
            $entityManager->flush();

            $update = new Update(
                'blog_listing',
                "
                <turbo-stream action=\"append\" target=\"my-fancy-table\">
                 <template>".
                    $this->renderView('blog_post/_row.html.twig', ['blog_post' => $blogPost])
                 ."</template>
                </turbo-stream>
                "
            );
            $hub->publish($update);

            return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog_post/new.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
        ]);
    }

    #[Route('/review/{id}', name: 'app_blog_post_review', methods: ['GET'])]
    public function review(BlogPost $blogPost, WorkflowInterface $blogPublishingStateMachine, EntityManagerInterface $em): Response
    {
        if ($blogPublishingStateMachine->can($blogPost, BlogPost::TRANSITION_TO_REVIEW)) {
            $blogPublishingStateMachine->apply($blogPost, BlogPost::TRANSITION_TO_REVIEW);
            $em->flush();
            $this->addFlash('success', 'Sent to review queue!');
        } else {
            $this->addFlash('danger', 'Cannot send to review queue!');
        }

        return $this->redirectToRoute('app_blog_post_show', ['id' => $blogPost->getId()]);
    }

    #[Route('/publish/{id}', name: 'app_blog_post_publish', methods: ['GET'])]
    public function publish(BlogPost $blogPost, WorkflowInterface $blogPublishingStateMachine, EntityManagerInterface $em): Response
    {
        if ($blogPublishingStateMachine->can($blogPost, BlogPost::TRANSITION_PUBLISH)) {
            $blogPublishingStateMachine->apply($blogPost, BlogPost::TRANSITION_PUBLISH);
            $em->flush();
            $this->addFlash('success', 'Sent to publish queue!');
        } else {
            $this->addFlash('danger', 'Cannot send to publish queue!');
        }

        return $this->redirectToRoute('app_blog_post_show', ['id' => $blogPost->getId()]);
    }

    #[Route('/reject/{id}', name: 'app_blog_post_reject', methods: ['GET'])]
    public function reject(BlogPost $blogPost, WorkflowInterface $blogPublishingStateMachine, EntityManagerInterface $em): Response
    {
        if ($blogPublishingStateMachine->can($blogPost, BlogPost::TRANSITION_REJECT)) {
            $blogPublishingStateMachine->apply($blogPost, BlogPost::TRANSITION_REJECT);
            $em->flush();
            $this->addFlash('success', 'Sent to reject queue!');
        } else {
            $this->addFlash('danger', 'Cannot send to reject queue!');
        }

        return $this->redirectToRoute('app_blog_post_show', ['id' => $blogPost->getId()]);
    }
    #[Route('/draft/{id}', name: 'app_blog_post_draft', methods: ['GET'])]
    public function draft(BlogPost $blogPost, WorkflowInterface $blogPublishingStateMachine, EntityManagerInterface $em): Response
    {
        if ($blogPublishingStateMachine->can($blogPost, BlogPost::TRANSITION_TO_DRAFT)) {
            $blogPublishingStateMachine->apply($blogPost, BlogPost::TRANSITION_TO_DRAFT);
            $em->flush();
            $this->addFlash('success', 'Sent back to draft!');
        } else {
            $this->addFlash('danger', 'Cannot send to to draft!');
        }

        return $this->redirectToRoute('app_blog_post_show', ['id' => $blogPost->getId()]);
    }

    #[Route('/{id}', name: 'app_blog_post_show', methods: ['GET'])]
    public function show(BlogPost $blogPost): Response
    {
        return $this->render('blog_post/show.html.twig', [
            'blog_post' => $blogPost,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogPost $blogPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog_post/edit.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_post_delete', methods: ['POST'])]
    public function delete(Request $request, BlogPost $blogPost, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blogPost->getId(), $request->request->get('_token'))) {
            $id = $blogPost->getId();
            $entityManager->remove($blogPost);
            $entityManager->flush();

            $update = new Update(
                'blog_listing',
                "
                <turbo-stream action=\"remove\" target=\"blog_" . $id . "\">
                 <template></template>
                </turbo-stream>
                "
            );
            $hub->publish($update);
        }

        return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/blog/test', name: 'app_blog_test', methods: ['GET'])]
    public function frame(): Response
    {
        $number = (string) rand(1, 500);

        return new Response(
            "<turbo-stream action=\"update\" target=\"test-stream\"><template>Is this thing on? $number</template></turbo-stream>",
            Response::HTTP_OK,
            [
                'content-type' => 'text/vnd.turbo-stream.html'
            ]
        );
    }

    #[Route('/blog/frame', name: 'app_blog_frame', methods: ['GET'])]
    public function testingFrame(): Response
    {
        $number = (string) rand(1, 500);

        return $this->render('blog_post/_frame.html.twig', ['num' => $number]);
    }

    #[Route('/blog/frame/load', name: 'app_blog_frame_load', methods: ['GET'])]
    public function loadingFrame(): Response
    {
        return $this->render('blog_post/_load.html.twig');
    }
}
