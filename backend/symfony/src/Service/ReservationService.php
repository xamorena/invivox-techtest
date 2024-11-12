<?php
namespace App\Service;

use App\DTO\ReservationDTO;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;

class ReservationException extends \Exception
{
  public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}

class ReservationService
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
  ) {
  }

  public function createReservation(ReservationDTO $reservationDTO): Reservation
  {
      $repository = $this->entityManager->getRepository(Reservation::class);
      # Création de la réservation
      $reservation = new Reservation();

      $foodtruck = $reservationDTO->foodtruck;
      $time = $reservationDTO->date;

      # Normalisation
      $foodtruck = strtoupper(trim($foodtruck));
      $date = date_create($time);
      $date1 = date_create($time);
      $date1->modify('monday this week');
      $date2 = date_create($time);
      $date2->modify('sunday this week');

      $existingReservations = $repository->findByNameAndPeriod(
        $foodtruck, 
        $date1->format('Y-m-d'), 
        $date2->format('Y-m-d')
      );
      if ($existingReservations) {
        throw new ReservationException('Ce foodtruck a déjà réservé un emplacement cette semaine.', 400);
      }

      # Vérification : limite de 8 emplacements du lundi au jeudi, 7 le vendredi
      $dayOfWeek = $date->format('N');
      $maxSpots = $dayOfWeek == 5 ? 7 : 8;

      $dailyReservations = $repository->countByDate($date);

      if (count($dailyReservations) >= $maxSpots) {
        throw new ReservationException('Nombre maximum d\'emplacements déjà réservés pour ce jour.', 400);
      }

      $reservation->setFoodtruck($reservationDTO->foodtruck);
      $reservation->setDate(new \DateTime($$reservationDTO->date()));
      $this->entityManager->persist($reservation);
      $this->entityManager->flush();
      return $reservation;
  }

  public function selectAllReservations(): array
  {
    return $this->entityManager->getRepository(Reservation::class)->findAll();
  }

  public function selectReservationsByDate(\DateTime $date): array
  {
    return $this->entityManager->getRepository(Reservation::class)->findBy(['date' => $date]);
  }

  public function selectOneReservation(int $id): Reservation
  {
    return $this->entityManager->getRepository(Reservation::class)->find($id);
  }

  public function deleteReservation(int $id): Reservation
  {
    $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);
    if (!$reservation) {
      throw new ReservationException("", 400, null);
    }
    $this->entityManager->remove($reservation);
    $this->entityManager->flush();
    return $reservation;
  }
}