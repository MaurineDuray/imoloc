<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des annonces
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();


        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

    #[Route("/ads/new", name:"ads_create")]
    #[IsGranted("ROLE_USER")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $ad = new Ad();

        $form = $this->createForm(AnnonceType::class, $ad);
        // permet de récupèrer la requête et l'état du formulaire
        $form->handleRequest($request);

        // Es-ce que mon formulaire à été soumis?
        if($form->isSubmitted() && $form->isValid())
        {
            // gestion des images 
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée!"
            );
          
            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render("ad/new.html.twig",[
            'myform' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier une annonce
     */
    #[Route("/ads/{slug}/edit", name:'ads_edit')]
    #[Security("(is_granted('ROLE_USER') and user === ad.getAuthor()) or is_granted('ROLE_ADMIN')", message: "Cette annonce ne vous appartient pas, vous le pouvez pas la modifier")]
    public function edit(Request $request, EntityManagerInterface $manager, Ad $ad):Response
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }
                //$ad->setSlug(""); // pour que le slug soit aussi modifié

                // on ajoute l'auteur mais attention risque de bug jusqu'à théorie sécurity
                $ad->setAuthor($this->getUser());

                $manager->persist($ad);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "L'annonce <strong> {$ad->getTitle()}</strong> a bien été modifiée"
                );

                return $this->redirectToRoute('ads_show',['slug'=>$ad->getSlug()]);
            
        }
        return $this->render("ad/edit.html.twig",[
            "ad"=>$ad,
            "myform"=>$form->createView()

        ]);

    }

    #[Route('/ads/{slug}/delete', name:"ads_delete")]
    #[Security("(is_granted('ROLE_USER') and user === ad.getAuthor()) or is_granted('ROLE_ADMIN')", message: "Cette annonce ne vous appartient pas, vous le pouvez pas la modifier")]
    public function delete(Ad $ad, EntityManagerInterface $manager):Response
    {
        //d'abord ceci pour avoir le title avant de supprimer
        $this->addFlash(
            'succes',
            "l'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée"
        );

        $manager->remove($ad);
        $manager->flush();

        return $this->redirectToRoute('ads_index');
    }
    /**
     * Permet d'afficher une annonce via son slug
     */
    #[Route('/ads/{slug}', name:'ads_show')]
    public function show(string $slug, Ad $ad):Response
    {
        // dump($ad);

        return $this->render('ad/show.html.twig',[
            'ad' => $ad
        ]);
    }


}