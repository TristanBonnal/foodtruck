<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Models\JsonError;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationController extends AbstractController
{
    /**
     * Add a reservation
     * 
     * @return Response
     * 
     * @Route("/api/reservations", name="api_add_reservation", methods = {"POST"})
     */
    public function addReservation(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Deserialization from json
        $data = $request->getContent();
        try {
            $newReservation = $serializer->deserialize($data, Reservation::class, "json");
            $newReservation->setUser($this->getUser());
        } catch (NotNormalizableValueException $e) {
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
     * Retourne les cagnottes liées à un utilisateur
     * 
     * @return Response
     * 
     * @Route("/api/Reservations", name="api_Reservations", methods = {"GET"})
     */
    public function ReservationsByUser(): Response
    {
        $Reservations = $this->getUser()->getReservations();

        return $this->json(
            $Reservations, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_Reservation']]
        );
    }

    /**
     * Récupère une cagnotte tout en vérifiant l'utilisateur associé
     * 
     * @return mixed
     * @param Reservation $Reservation cagnotte correspondant au paramètre dynamique
     * 
     * @Route("/api/Reservations/{id}", name="api_show_Reservation", methods = {"GET"})
     */
    public function showReservation(Reservation $Reservation = null): Response
    {
        // Vérification de la cagnotte et de l'utilisateur
        try {
            if (!$Reservation) {
                throw new Exception("Cette cagnotte n'existe pas (identifiant erroné)", RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $Reservation->getUser(), "Vous n'avez pas accès à cette cagnotte");
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return $this->json(
            $Reservation, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_Reservation']]
        );
    }

}