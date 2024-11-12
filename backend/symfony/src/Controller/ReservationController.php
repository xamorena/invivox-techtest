<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\DTO\ReservationDTO;
use App\Service\ReservationException;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;


class ReservationController extends AbstractController
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {
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
    public function createReservation(
        #[MapRequestPayload] ReservationDTO $reservationDTO,
    ): JsonResponse {
        try {
            $reservation = $this->reservationService->createReservation($reservationDTO);
            return $this->json($reservation);
        } catch (ReservationException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/reservations", name: "select_reservations", methods: ["GET"])]
    #[OA\Tag(name: 'reservation')]
    public function selectAllReservations(): JsonResponse
    {
        try {
            $reservations = $this->reservationService->selectAllReservations();
            if (!$reservations) {
                return $this->json([]);
            }
            return $this->json($reservations);
        } catch(ReservationException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } 
    }

    #[Route("/api/reservations/{id}", name: "delete_reservation", methods: ["DELETE"])]
    #[OA\Tag(name: 'reservation')]
    public function deleteReservation(int $id): JsonResponse
    {
        try {
            $reservation = $this->reservationService->deleteReservation($id);
            return $this->json($reservation);
        } catch(ReservationException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route("/api/reservations/{date}", name: "list_reservations", methods: ["GET"])]
    #[OA\Tag(name: 'reservation')]
    public function getReservationsByDate(string $date): JsonResponse
    {
        try {
            $time = new \DateTime($date);
            $reservations = $this->reservationService->selectReservationsByDate($time);
            return $this->json($reservations);
        } catch(\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

}

