from fastapi import APIRouter
from pyfoods import reservations

api_router = APIRouter()
api_router.include_router(reservations.router, prefix="/reservations")


@api_router.get("/")
async def api_home():
    """
    API v1.0
    """
    return dict(status="Ok", version="1.0")
