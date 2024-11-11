import logging
from datetime import datetime, timedelta
from typing import List, Optional

from fastapi.responses import JSONResponse
from pydantic import BaseModel

from fastapi import APIRouter, HTTPException
from pyfoods.prisma.client import prisma_client
from pyfoods.settings import config

logger = logging.getLogger("pyfoods.reservations")


class ReservationMessages:
    ERR_MSG_001 = "The reservation cannot be validated, this food truck is already registered this week"
    ERR_MSG_002 = "Maximum number of locations already reserved for that day"
    ERR_MSG_003 = "Reservation id not found"


class ReservationException(HTTPException):
    pass


class ReservationForm(BaseModel):
    date: datetime
    foodtruck: str


class ReservationBase(ReservationForm):
    id: int


router = APIRouter()


@router.post("/", response_model=ReservationForm, tags=["reservations"])
async def create_reservation(reservation_form: ReservationForm):
    """
    Creation d'une reservation
    """
    # Normalise le nom du foodtruck
    reservation_form.foodtruck = reservation_form.foodtruck.strip().upper()
    logger.info(f"Creating reservation for foodtruck={reservation_form.foodtruck}, date={reservation_form.date.date()}")
    try:
        # Règle 1: Un foodtruck ne peut réserver qu'une fois par semaine
        date1 = reservation_form.date - timedelta(days=1 + reservation_form.date.weekday())
        date2 = reservation_form.date + timedelta(days=7 - reservation_form.date.weekday())
        logger.info(f"Finding foodtruck reservation between {date1} and {date2}")
        existing_reservations = await prisma_client.reservation.find_many(
            where={
                "foodtruck": reservation_form.foodtruck,
                "date": {
                    # début de la semaine
                    "gte": date1,
                    # fin de la semaine
                    "lte": date2,
                },
            }
        )
        logger.info(
            f"Existing reservations found: {len(existing_reservations) if existing_reservations else None}"
        )

        if existing_reservations:
            raise ReservationException(
                status_code=400, detail=ReservationMessages.ERR_MSG_001
            )

        # Règle 2: Limite de 8 emplacements du lundi au jeudi et 7 le vendredi
        day_of_week = reservation_form.date.weekday()
        spot_locations = (
            config.WEEK_LIMITS[day_of_week]
            if day_of_week in range(len(config.WEEK_LIMITS))
            else None
        )
        logger.info(f"Available locations: {len(spot_locations)}")

        if spot_locations:
            max_spots = len(spot_locations)
        else:
            max_spots = 7 if day_of_week == 4 else 8
        logger.info(f"Maximumn locations: {max_spots}")
        date1 = reservation_form.date - timedelta(hours=1 + reservation_form.date.hour)
        date2 = reservation_form.date + timedelta(hours=24 - reservation_form.date.hour)
        logger.info(f"Finding daily reservations between {date1} and {date2}")
        daily_reservations = await prisma_client.reservation.find_many(
            where={
                "date": {
                    # début de la journée
                    "gte": date1,
                    # fin de la journée
                    "lte": date2,
                }
            }
        )
        logger.info(f"Daily reservations found: {len(daily_reservations)}")

        if len(daily_reservations) >= max_spots:
            raise ReservationException(
                status_code=400, detail=ReservationMessages.ERR_MSG_002
            )
        reservation = await prisma_client.reservation.create(reservation_form.__dict__)
        logger.info("reservations created")
        return reservation
    except ReservationException as e:
        return JSONResponse(status_code=e.status_code, content={"detail": e.detail})
    except Exception as e:
        logger.error(f"create reservation error: {e}")
        raise HTTPException(status_code=500, detail=f"{e}")


@router.get("/", response_model=List[ReservationBase], tags=["reservations"])
async def read_all_reservations():
    """
    Lecture de toute les reservations
    """
    try:
        reservations = await prisma_client.reservation.find_many()
        return reservations
    except Exception as e:
        logger.error(f"create reservation error: {e}")
        raise HTTPException(status_code=500, detail=f"{e}")


@router.get(
    "/{reservation_name}",
    response_model=Optional[List[ReservationBase]],
    tags=["reservations"],
)
async def read_reservations(reservation_name: str):
    """
    lecture des reservations via le nom du foodtruck
    """
    try:
        # Normalise le nom du foodtruck
        reservation_name = reservation_name.strip().upper()
        reservations = await prisma_client.reservation.find_many(
            where={"foodtruck": reservation_name}
        )
        return reservations
    except Exception as e:
        logger.error(f"create reservation error: {e}")
        raise HTTPException(status_code=500, detail=f"{e}")


@router.get(
    "/{reservation_name}/{reservation_date}",
    response_model=Optional[List[ReservationBase]],
    tags=["reservations"],
)
async def read_reservations_by_date(reservation_name: str, reservation_date: datetime):
    """
    lecture des reservations via le nom et un date
    """
    try:
        # Normalise le nom du foodtruck
        reservation_name = reservation_name.strip().upper()
        reservations = await prisma_client.reservation.find_many(
            where={
                "foodtruck": reservation_name,
                "date": {
                    "gte": reservation_date
                    - timedelta(hours=reservation_date.hour),  # début de la journée
                    "lte": reservation_date
                    + timedelta(hours=24 - reservation_date.hour),  # fin de la journée
                },
            }
        )
        return reservations
    except Exception as e:
        logger.error(f"list reservations error: {e}")
        raise HTTPException(status_code=500, detail=f"{e}")


@router.delete("/{reservation_id}", tags=["reservations"])
async def delete_reservation(reservation_id: int):
    """
    Suppression d'une reservation
    """
    try:
        reservation = await prisma_client.reservation.find_first(
            where={"id": reservation_id}
        )
        if reservation is not None:
            await prisma_client.reservation.delete(where={"id": reservation_id})
        else:
            raise ReservationException(
                status_code=401, detail=ReservationMessages.ERR_MSG_003
            )
    except ReservationException as e:
        return JSONResponse(status_code=e.status_code, content={"detail": e.detail})
    except Exception as e:
        logger.error(f"delete reservation error: {e}")
        raise HTTPException(status_code=500, detail=f"{e}")
