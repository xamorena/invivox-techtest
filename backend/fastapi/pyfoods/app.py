import logging
import os
from contextlib import asynccontextmanager

from fastapi.responses import RedirectResponse, Response
from fastapi.staticfiles import StaticFiles
from starlette.middleware.cors import CORSMiddleware
from starlette.middleware.sessions import SessionMiddleware
from starlette.types import Scope

from fastapi import FastAPI
from pyfoods.api import api_router
from pyfoods.prisma.client import prisma_client
from pyfoods.settings import config

logger = logging.getLogger("pyfoods")


class SPA(StaticFiles):
    async def get_response(self, path: str, scope: Scope) -> Response:
        try:
            return await super().get_response(path, scope)
        except Exception as e:
            logger.warning(f"redirect to index.html: {e}")
            return await super().get_response("index.html", scope)


@asynccontextmanager
async def lifespan(app: FastAPI):
    await prisma_client.connect()
    yield
    await prisma_client.disconnect()


def create_app():
    spa = os.getenv("BASE_URL", None)
    app = FastAPI(
        lifespan=lifespan,
        title="PyFoods",
        version="1.0.0",
        summary="Foodtrucks reservations service",
        description="Foodtrucks reservations based on FastAPI/Prisma",
        docs_url="/api/doc",
        redoc_url="/api/redoc",
        openapi_url="/api/openapi.json",
    )
    app.add_middleware(
        CORSMiddleware,
        allow_origins=[config.CORS_ALLOW_ORIGN],
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )
    app.add_middleware(SessionMiddleware, secret_key=config.SECRET_KEY)

    if spa is not None:
        if spa not in [""]:

            @app.get("/")
            async def local_forward():
                return RedirectResponse(url=f"/{spa}", status_code=301)

            app.mount(
                f"/{spa}",
                SPA(
                    directory=os.path.join(os.path.dirname(__file__), "spa"), html=True
                ),
                name="SPA",
            )

    app.include_router(api_router, prefix="/api")
    return app
