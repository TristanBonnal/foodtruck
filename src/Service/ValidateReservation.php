<?php
namespace App\Service;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ValidateReservation
{
    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     * Check if the actual reservation spot is free for the requested booking date
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkSpot(Reservation $reservation)
    {
        $reservationsByDate = $this->reservationRepository->findReservationsByDateAndSpot($reservation);
        if (!empty($reservationsByDate)) {
            throw new Exception("Emplacement n° {$reservation->getSpot()} déjà réservé à cette date", Response::HTTP_BAD_REQUEST); 
        }
    }

    /**
     * Check the number of reservations by day and that bookedAt >= +1 day
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkByDay(Reservation $reservation)
    {
        $reservationsByDate = $this->reservationRepository->findReservationsByDate($reservation);
        $dayOfTheWeek = $reservation->getBookedAt()->format('l');
        $minDate = (new \DateTime('tomorrow'));
        
        // Numbers of reservations limits
        if (count($reservationsByDate) >= 7 || $dayOfTheWeek == 'Friday' && count($reservationsByDate) >= 6) {
            throw new Exception("Limite du nombre de réservations atteinte à cette date", Response::HTTP_BAD_REQUEST); 
        }

        // Soonest date possible
        if ($reservation->getBookedAt() < $minDate) {
            throw new Exception("Vous pouvez réserver un emplacement demain au plus tot", Response::HTTP_BAD_REQUEST); 
        }
    }

    /**
     * Check if the user already made a reservation during the week he targets
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkByUserAndByWeek(Reservation $reservation)
    {
        $reservationsByUser = $this->reservationRepository->findReservationsByUser($reservation);
        $requestedWeek = $reservation->getBookedAt()->format('W');
        $requestedYear = $reservation->getBookedAt()->format('Y');
        
        foreach ($reservationsByUser as $value) {
            if ($value->getBookedAt()->format('W') == $requestedWeek && $value->getBookedAt()->format('Y') == $requestedYear) {
                throw new Exception("Une seule réservation par semaine autorisée", Response::HTTP_BAD_REQUEST); 
            }
        }
    }
}