<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AdController extends AbstractController
{
    /**
     * PErmet d'afficher l'ensemble des annonces
     *
     * @param AdRepository $repo
     * @return Response
     */
    #[Route('/ads', name: 'ads_index')]
    public function index(AdRepository $repo): Response
    {
        $ads = $repo -> findAll();
        return $this->render('ad/index.html.twig', [
            'ads' => $ads, //envoie toutes les annonces
        ]);
    }

    // #[Route('/ads/{slug}', name:'ads_show')]
    // public function show(string $slug ,ManagerRegistry $doctrine):Response
    // {
    //     $repo = $doctrine->getRepository(Ad::class); // ancienne méthode
    //     $ad = $repo->findOneBySlug($slug);
    //     return $this->render('ad/show.html.twig',[
    //         'ad'=> $ad 
    //     ]);
    // }

   
    #[Route('ads/new', name:"ads_create")]
    public function create(): Response
    {
        $ad = new Ad(); // sur quoi porte le formulaire 

        // $form = $this-> createFormBuilder($ad) // crée un formulaire sur qui pour les champs qui suivent
        //     ->add('title')
        //     ->add('introduction')
        //     ->add('content')
        //     ->add('rooms')
        //     ->add('price')
        //     ->add('save', SubmitType::class, [
        //         'label' =>"créer la nouvelle annonce",
        //         "attr" =>[
        //             'class' => 'btn btn-primary'
        //         ]
        //     ]) //bouton envoyer
        //     ->getForm();

            // $form = $this-> createFormBuilder($ad) // crée un formulaire sur qui pour les champs qui suivent
            // ->add('title')
            // ->add('introduction')
            // ->add('content')
            // ->add('rooms')
            // ->add('price')
            
            // ->getForm();

            // externaliser toutes les lignes commentées dans src>form>AnnonceType

            $form = $this->createForm(AnnonceType::class, $ad);

        return $this->render("ad/new.html.twig",[
            'myform'=> $form->createView()  //affiche le formulaire (le visuel)
        ]);
    }


    //nouvelle methode -  détecte le slug automatiquement dans l'url param converter
    /**
     * Permet d'afficher une annonce via son slug 
     */
    #[Route('/ads/{slug}', name:'ads_show')]
    public function show(string $slug ,Ad $ad):Response
    {
       
        dump($ad);

        return $this->render('ad/show.html.twig',[
            'ad'=> $ad 
        ]);
    }
}
