<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;

use DateTimeImmutable;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // gestion du hash du password 
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');

        //création d'un admin 
        $admin = new User();
        $admin->setFirstName('Maurine')
            ->setLastName('Duray')
            ->setEmail('maurine.duray@proximus.be')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
            ->setIntroduction($faker->sentence())
            ->setPicture('admin.png')
            ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        //gestion de mes utilisateurs 
        $users = []; //init un tableau pour récup les user pour les Ad
        $genres = ['male','femelle'];

        for($u=1; $u<=10 ; $u++)
        {
            $user = new User();
            $genre = $faker->randomElement($genres);

            // $picture = 'https://randomuser.me/api/portraits/';
            // $pictureId = $faker->numberBetween(1,99).'jpg';
            // $picture.=($genre == 'male' ? 'men/' : 'women/').$pictureId;

            $hash = $this->passwordHasher->hashPassword($user,'password');

            $user->setFirstName($faker->firstName($genre))
                -> setLastName($faker->lastName())
                -> setEmail($faker->email())
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
                ->setPassword($hash);

            $manager->persist($user);
            $users[] = $user;
        }
       

        for($i=1; $i <= 30 ; $i++)
        {
            $ad = new Ad();
            $title = $faker->sentence();
            $introduction = $faker->paragraph(2);
            $content = '<p>'.join('</p><p>',$faker->paragraphs(5)).'</p>';

            //lisaison avec user
            $user = $users[rand(0, count($users)-1)];

            $ad->setTitle($title)
                ->setCoverImage('https://picsum.photos/1000/350')
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(rand(40,200))
                ->setRooms(rand(1,5))
                ->setAuthor($user);


            $manager->persist($ad);

            //gestion de l'image de l'annonce
            for($j=1; $j<=rand(2,5) ; $j++)
            {
                $image = new Image();
                $image ->setUrl('https://picsum.photos/200/200')
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);
            }

            //gestion des réservations
            for($b=1; $b<=rand(0,10); $b++)
            {
                $booking = new Booking();
                $createAt = $faker->dateTimeBetween('-6 months', '-4 months');
                $createdAt = new DateTimeImmutable($createAt->format('Y-m-d'));
                $startDate = $faker->dateTimeBetween('-3 months');
                $duration = rand(3,10);
                //startDate est mnt un objet de type DateTime
                $endDate = (clone $startDate)->modify("+$duration days");$amount = $ad->getPrice()*$duration;
                $booker = $users[rand(0,count($users)-1)];
                $comment = $faker->paragraph();

                $booking->setBooker($booker)
                    ->setAd($ad)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setCreatedAt($createdAt)
                    ->setAmount($amount)
                    ->setComment($comment);

                $manager->persist($booking);
                $comment = new Comment();
                $comment -> setContent($faker->paragraph())
                ->setRating(rand(1,5))
                ->setAuthor($booker)
                ->setAd($ad);
                $manager->persist($comment);
            }
        }
        $manager->flush();
    }
}
