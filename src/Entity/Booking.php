<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $booker = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ad $ad = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan("today", message:'Doit être ultérieure à aujourd\'hui', groups:['front'])]
    #[Assert\Type('datetime')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath:"startDate", message:"La date de départ doit être plus éloignée que la date d'arrivée")]
    #[Assert\Type('datetime')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * Permet d'ajouter la date de création et de calculer le prix de la réservation
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePersist():void
    {
        if(empty($this->createdAt))
        {
            $this->createdAt = new \DateTimeImmutable();

        }
        if(empty($this->amount))
        {
            $this->amount = $this->ad->getPrice()*$this->getDuration();
        }
    }

    public function getDuration():?int
    {
        // la méthode diff des objets dattime fait la différence entre 2 dates et renvoie un objet de type DateInterval
        $diff = $this->endDate->diff($this->startDate);
        return $diff->days;
    }

   /**
     * PErmet de vérifier si les dates sont réservables
     *
     * @return boolean|null
     */
    public function isBookableDates(): ?bool 
    {
        // connaîtres les dates impossibles pour l'annonce (voir dans Ad)
        $notAvailableDays = $this->ad->getNotAvailableDays();
        // comparer les dates choisies avec les dates impossible 
        $bookingDays = $this->getDays();
        // transfomer des objets dateTime en tableau de chaines de caractères pour les journées (pour faciliter la comparaison)
        $days = array_map(function($day){
            return $day->format('Y-m-d');
        },$bookingDays);

        $notAvailable = array_map(function($day){
            return $day->format('Y-m-d');
        },$notAvailableDays);

        foreach($days as $day)
        {
            if(array_search($day, $notAvailable) !== false) return false;
        }

        return true;
    }

    /**
     * Permet de récup un tableau des journées qui correspondent à ma réservation
     *
     * @return array|null
     */
    public function getDays(): ?array
    {
        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60
        );
        $days = array_map(function($dayTimestamp){
            return new \DateTime(date('Y-m-d',$dayTimestamp));
        },$resultat);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
