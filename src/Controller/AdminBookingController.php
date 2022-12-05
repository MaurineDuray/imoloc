<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminBookingController extends AbstractController
{
    /**
     * Permet d'afficher les réservations de l'administration
     *
     * @param [type] $page
     * @param PaginationService $paginationService
     * @return Response
     */
    #[Route('/admin/bookings/{page<\d+>?1}', name: 'admin_bookings_index')]
    public function index($page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Booking::class)
            ->setPage($page)
            ->setLimit(10)//pas obligatoire car il est défini par défaut dans le PaginationService
            ->setOrder(["amount"=>"DESC"]);

        return $this->render('admin/booking/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Permet d'éditer une réservation 
     */
    #[Route('/admin/bookings/{id}/edit', name:"admin_bookings_edit")]
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager):Response
    {
        $form=$this->createForm(AdminBookingType::class, $booking, [
            'validation_groups'=>['Default']
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //0 = empty -> donc la fonction dans le PrePersist et PreUdpadte de l'entity booking va fonctionner 
            $booking->setAmount(0);
            $manager->persist($booking);
            $manager->flush();

            $this->addFlash(
                'success',
                "LA réservation n° <strong>{$booking->getId()} a bien été modifiée</strong>"
            );
        }

        return $this->render("admin/booking/edit.html.twig", [
            'booking'=> $booking,
            "myForm" => $form->createView()
        ]);

    }

    /**
     * Permet de supprimer une réservation
     *
     * @param Booking $booking
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/bookings/{id}/delete', name:'admin_bookings_delete')]
    public function delete(Booking $booking, EntityManagerInterface $manager):Response
    {

        $this->addFlash(
            "success",
            "La réservation n° <strong>{$booking->getId()}</strong> a bien été supprimée"
        );

        $manager -> persist($booking);
        $manager->flush();

        return $this->redirectToRoute("admin_bookings_index");

    }
}
