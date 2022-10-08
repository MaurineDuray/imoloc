<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Repository\AdRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
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

    //nouvelle methode -  détecte le slug automatiquement dans l'url param converter
    #[Route('/ads/{slug}', name:'ads_show')]
    public function show(string $slug ,Ad $ad):Response
    {
       
        return $this->render('ad/show.html.twig',[
            'ad'=> $ad 
        ]);
    }
}
