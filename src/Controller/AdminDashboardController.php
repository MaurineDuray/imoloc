<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard_index')]
    public function index(StatsService $stats): Response
    {

        $users = $stats->getUsersCount();
        // getSingleScalarResult() Permet de récupérer une valeur et pas un array 
        $ads=$stats->getAdsCount();
        $bookings=$stats->getBookingsCount();
        $comments=$stats->getCommentsCount();

        $bestAds = $stats->getAdsStats('DESC');

        $worstAds = $stats->getAdsStats('ASC');

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'users'=>$users,
                'ads'=>$ads,
                'bookings' =>$bookings,
                'comments'=>$comments
            ],
            'bestAds'=>$bestAds,
            'worstAds'=>$worstAds
        ]);
    }
}
