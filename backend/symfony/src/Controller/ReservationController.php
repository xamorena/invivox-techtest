<?php

namespace App\Controller;

use App\Entity\Reservation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

class ReservationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/", name: "home", methods: ["GET"])]
    public function renderHome(Request $request): Response
    {
        return $this->render('base.html.twig', ['title' => 'Welcome']);
    }

    #[Route("/api/reservations", name: "create_reservation", methods: ["POST"])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            required: ['date', 'foodtruck'],
            properties: [
                new OA\Property(property: 'date', type: 'datetime'),
                new OA\Property(property: 'foodtruck', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Create a reservation',
        content: new OA\JsonContent(ref: new Model(type: Reservation::class, groups: ['default']))
    )]
    #[OA\Tag(name: 'reservation')]
    public function createReservation(Request $request): JsonResponse
    {
        # Création de la réservation
        $reservation = new Reservation();
        $body = $request->getContent();
        $data = json_decode($body, true);

        $foodtruck = $data['foodtruck'];
        $time = $data['date'];

        # Normalisation
        $foodtruck = strtoupper(trim($foodtruck));

        $date = date_create($time);

        $date1 = date_create($time);
        $date1->modify('monday this week');

        $date2 = date_create($time);
        $date2->modify('sunday this week');

        $repository = $this->entityManager->getRepository(className: Reservation::class);
        $existingReservations = $repository->findByNameAndPeriod($foodtruck, $date1->format('Y-m-d'), $date2->format('Y-m-d'));

        if ($existingReservations) {
            return new JsonResponse(['error' => 'Ce foodtruck a déjà réservé un emplacement cette semaine.'], Response::HTTP_BAD_REQUEST);
        }

        # Vérification : limite de 8 emplacements du lundi au jeudi, 7 le vendredi
        $dayOfWeek = $date->format('N');
        $maxSpots = $dayOfWeek == 5 ? 7 : 8;

        $dailyReservations = $repository->countByDate($date);

        if (count($dailyReservations) >= $maxSpots) {
            return new JsonResponse(['error' => 'Nombre maximum d\'emplacements déjà réservés pour ce jour.'], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setDate($date);
        $reservation->setFoodtruck($foodtruck);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'id' => $reservation->getId(),
                'date' => $reservation->getDate()->format('Y-m-d\TH:i:sO'),
                'foodtruck' => $reservation->getFoodtruck()
            ]
            ,
            Response::HTTP_CREATED
        );
    }

    #[Route("/api/reservations", name: "select_reservations", methods: ["GET"])]
    #[OA\Tag(name: 'reservation')]
    public function selectAllReservations(): JsonResponse
    {
        $reservations = $this->entityManager->getRepository(Reservation::class)->findAll();

        if (!$reservations) {
            return new JsonResponse([]);
        }
        $response = array_map(function (Reservation $reservation) {
            return [
                'id' => $reservation->getId(),
                'date' => $reservation->getDate()->format('Y-m-d\TH:i:sO'),
                'foodtruck' => $reservation->getFoodtruck(),
            ];
        }, $reservations);
        return new JsonResponse($response);
    }

    #[Route("/api/reservations/{id}", name: "delete_reservation", methods: ["DELETE"])]
    #[OA\Tag(name: 'reservation')]
    public function deleteReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Réservation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return new JsonResponse(['detail' => 'Réservation supprimée avec succès.']);
    }

    #[Route("/api/reservations/{date}", name: "list_reservations", methods: ["GET"])]
    #[OA\Tag(name: 'reservation')]
    public function getReservationsByDay(string $date): JsonResponse
    {
        $dateObj = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(['date' => $dateObj]);

        $response = array_map(function (Reservation $reservation) {
            return [
                'id' => $reservation->getId(),
                'date' => $reservation->getDate()->format('Y-m-d\TH:i:sO'),
                'foodtruck' => $reservation->getFoodtruck(),
            ];
        }, $reservations);

        return new JsonResponse($response);
    }
}

