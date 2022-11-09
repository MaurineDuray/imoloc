<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    #[Route('/ads/{slug}/book', name: 'booking_create')]
    #[IsGranted('ROLE_USER')]
    public function book(Ad $ad, Request $request, EntityManagerInterface $manager): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);// vérifie si formulaire soumis et validé (état)

        if($form->isSubmitted() && $form->isValid())
        {
            //traitement
            $user = $this->getUSer();// je suis certain qu'il y a un user car j'ai sécurisé la fonction
            $booking->setBooker($user)
                ->setAd($ad);

            //vérifier les disponibilité des dates + message d'erreur
            if(!$booking->isBookableDates()){
                $this->addFlash(
                    'warning',
                    'Les dates que vous avez choisies ne peuvent être réservées car elles sont déjà prises'
                );
            }else{
                $manager->persist($booking);
                $manager->flush();

                return $this->redirectToRoute('booking_show',['id'=>$booking->getId(), 'withAlert'=>true]); // j'ai un id comme il a été flush
            }
        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'myform'=>$form->createView()
        ]);
    }

    /**
     * Permet l'affcihage de la page de réservation
     */
    #[Route("/booking/{id}", name:"booking_show")]
    #[IsGranted("ROLE_USER")]
    public function show(Booking $booking): Response
    {
        return $this->render("booking/show.html.twig",[
            'booking'=>$booking
        ]);
    }
}
