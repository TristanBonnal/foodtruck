<?php

namespace App\Controller\Api;

use App\Entity\Pot;
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

class PotController extends AbstractController
{
    /**
     * Ajoute une cagnotte via un formulaire
     * 
     * @return Response
     * 
     * @Route("/api/pots", name="api_add_pot", methods = {"POST"})
     */
    public function addPot(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        // Deserialisation du contenu du formulaire
        $data = $request->getContent();
        try {
            $newPot = $serializer->deserialize($data, Pot::class, "json");
            // Type défini à souple si aucun objectif
            if (empty($newPot->getAmountGoal()) && empty($newPot->getDateGoal())) {
                $newPot->setType(0);
            }
            $newPot->setUser($this->getUser());
        } catch (NotNormalizableValueException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Vérification des données formualaire
        $errors = $validator->validate($newPot);
        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->persist($newPot);
        $doctrine->flush();

        return $this->json(
            $newPot, Response::HTTP_CREATED,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * Retourne les cagnottes liées à un utilisateur
     * 
     * @return Response
     * 
     * @Route("/api/pots", name="api_pots", methods = {"GET"})
     */
    public function potsByUser(): Response
    {
        $pots = $this->getUser()->getPots();

        return $this->json(
            $pots, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * Récupère une cagnotte tout en vérifiant l'utilisateur associé
     * 
     * @return mixed
     * @param Pot $pot cagnotte correspondant au paramètre dynamique
     * 
     * @Route("/api/pots/{id}", name="api_show_pot", methods = {"GET"})
     */
    public function showPot(Pot $pot = null): Response
    {
        // Vérification de la cagnotte et de l'utilisateur
        try {
            if (!$pot) {
                throw new Exception("Cette cagnotte n'existe pas (identifiant erroné)", RESPONSE::HTTP_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('USER', $pot->getUser(), "Vous n'avez pas accès à cette cagnotte");
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        return $this->json(
            $pot, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }

    /**
     * 
     * Permet de modifier une cagnotte déja existante
     * 
     * @return response
     * @param Pot $pot cagnotte correspondant au paramètre dynamique
     *
     * @Route("/api/pots/{id}", name="api_update_pot", methods = {"PATCH"})
     */
    public function updatePot(Pot $pot = null,EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();

        // Si la cagnotte n'existe pas, on renvoie une erreur
        try {
            if (!$pot) {
                throw new Exception("Cette cagnotte n'existe pas (identifiant erroné)", RESPONSE::HTTP_NOT_FOUND);
            }
        // Seul le créateur de la cagnotte a accès à celle-ci
            $this->denyAccessUnlessGranted('USER', $pot->getUser(), "Vous n'avez pas accès à cette cagnotte");
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode());
        }

        // On récupère les informations du formulaire et on désérialize, sinon erreur
        try {
            $newPot = $serializer->deserialize($data, Pot::class, "json");
            $newPot->setUser($this->getUser());
        } catch (NotNormalizableValueException $e) {
            return new JsonResponse("Erreur de type pour le champ '". $e->getPath() . "': " . $e->getCurrentType() . " au lieu de : " . implode('|', $e->getExpectedTypes()), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pot
            ->setName($newPot->getName())
            ->setDateGoal($newPot->getDateGoal())
            ->setAmountGoal($newPot->getAmountGoal())
            ->setType($newPot->getType())
            ->setUpdatedAt(new \DateTime)
        ;

        // Vérification des données du formulaire
        $errors = $validator->validate($newPot);

        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->flush();  
       
        return $this->json(
            $pot, 
            Response::HTTP_OK,
            [],
            ['groups' => ['show_pot']]
        );
    }
}