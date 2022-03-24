<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Models\JsonError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

    /**
     * Allow user to signup
     * 
     * @return Response
     *
     * @Route("/api/signup", name="api_signup", methods = {"POST"})
     */
    public function signUp(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $hasher ): Response
    {
        $data = $request->getContent();
        try {
            $newUser = $serializer->deserialize($data, User::class, "json");
        } catch (NotNormalizableValueException $e) {
            return new JsonResponse("Erreur de type pour le champ '". $e->getPath() . "': " . $e->getCurrentType() . " au lieu de : " . implode('|', $e->getExpectedTypes()), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Checking form datas
        $errors = $validator->validate($newUser);

        if (count($errors) > 0) {
            // Custom Json errors
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);
            return $this->json($myJsonError, $myJsonError->getError());
        }
        $hashedPassword = $hasher->hashPassword($newUser, $newUser->getPassword());
        $newUser->setPassword($hashedPassword);

        $doctrine->persist($newUser);
        $doctrine->flush();

        // Returns 201
        return $this->json(
            $newUser, Response::HTTP_CREATED,
            [],
            ['groups' => ['show_user']]
        );
    }

    /**
     * Retourne les utilisateurs
     *
     * @return Response
     *
     * @Route("/api/users", name="api_show_user", methods = {"GET"})
     */
    public function showUser(): Response
    {
        return $this->json(
            $this->getUser(),
            Response::HTTP_OK,
            [],
            ['groups' => ['show_user']]
        );
    }

}