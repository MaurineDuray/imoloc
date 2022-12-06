<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Affiche tous les users
     *
     * @param UserRepository $repo
     * @return Response
     */
    #[Route('/admin/user/{page<\d+>?1}', name: 'admin_user_index')]
    public function index(PaginationService $pagination, $page): Response
    {
        $pagination->setEntityClass(User::class)
            ->setPage($page)
            ->setLimit(10);//pas obligatoire car il est défini par défaut dans le PaginationService

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    
   
    /**
     * Permet de modifier un user
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/users/{id<\d+>?2}/edit', name:"admin_users_edit")]
    public function edit(User $user, Request $request, EntityManagerInterface $manager):Response
    {
        $form=$this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "L'utilisateur <strong>{$user->getId()}-{$user->getFullName()}</strong> a bien été modifié"
            );
        }

        return $this->render('admin/user/edit.html.twig',[
            "user"=>$user,
            "myForm"=>$form->createView()
        ]);
    }

    /**
     * Permet de supprimer un user
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return void
     */
    #[Route('/admin/users/{id}/delete', name:"admin_users_delete")]
    public function delete(User $user, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "L'utilisateur {$user->getId()} a bien été supprimé"
        );

        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute("admin_user_index");
    }
}
