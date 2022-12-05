<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentController extends AbstractController
{
    
    /**
     * Afficher les commentaires pour l'admin
     *
     * @param CommentRepository $repo
     * @return Response
     */
    #[Route('/admin/comment/{page<\d+>?1}', name: 'admin_comments_index')]
    public function index(PaginationService $pagination, $page): Response
    {
        $pagination->setEntityClass(Comment::class)
            ->setPage($page)
            ->setLimit(10);

        return $this->render('admin/comment/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Permet de modifier un comment 
     *
     * @param Comment $comment
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/comments/{id}/edit', name:"admin_comments_edit")]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager):Response
    {
        $form=$this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid())
        {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                "success",
                "Le commentaire <strong>{$comment->getId()}</strong> a bien été modifié"
            );
        }

        return $this->render("admin/comment/edit.html.twig",[
            "comment" => $comment,
            "myform"=>$form->createView()
        ]);
    }

    /**
     * PErmet de supprimer un commentaire 
     *
     * @param Comment $comment
     * @param EntityManagerInterface $manager
     * @return void
     */
    #[Route('/admin/comments/{id}/delete', name:"admin_comments_delete")]
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le commentaire {$comment->getId()} a bien été supprimé"
        );

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute("admin_comments_index");
    }
}
