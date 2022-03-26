<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Models\JsonError;
use App\Service\ValidateReservation;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationController extends AbstractController
{
    /**
     * Add a reservation
     * Json:
     * {
     *     "bookedAt": "2022-05-03",
     *     "spot": 3
     *  }
     * 
     * @return Response
     * 
     * @Route("/api/reservations", name="api_add_reservation", methods = {"POST"})
     */
    public function addReservation(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ValidateReservation $resaValidator): Response
    {
        // Deserialization from json
        $data = $request->getContent();
        try {
            $newReservation = $serializer->deserialize($data, Reservation::class, "json");
        } catch (Exception $e) {
            return new JsonResponse('Veuillez compléter tous les champs', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $newReservation->setUser($this->getUser());

            // Check all conditions to validate the reservation (see Service)
            $resaValidator->checkSpot($newReservation);
            $resaValidator->checkByDay($newReservation);
            $resaValidator->checkByUserAndByWeek($newReservation);

        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Checking form datas
        $errors = $validator->validate($newReservation);
        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->persist($newReservation);
        $doctrine->flush();

        return $this->json(
            $newReservation, Response::HTTP_CREATED,
            [],
            ['groups' => ['show_reservation']]
        );
    }

    /**
     * Returns reservations from the logged user
     * 
     * @return Response
     * 
     * @Route("/api/reservations", name="api_reservations", methods = {"GET"})
     */
    public function reservationsByUser(): Response
    {
        $reservations = $this->getUser()->getReservation();

        return $this->json(
            $reservations, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_reservation']]
        );
    }

    /**
     * Returns a reservation found by id after checking the user requesting it
     * 
     * @return mixed
     * @param Reservation $reservation
     * 
     * @Route("/api/reservations/{id}", name="api_show_reservation", methods = {"GET"})
     */
    public function showReservation(Reservation $reservation = null): Response
    {
        // Checking the reservation 
        try {
            if (!$reservation) {
                throw new Exception("Cette réservation n'existe pas (identifiant erroné)", RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $reservation->getUser(), "Vous n'avez pas accès à cette réservation");
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return $this->json(
            $reservation, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_reservation']]
        );
    }

}