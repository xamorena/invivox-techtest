import pytest
from fastapi.testclient import TestClient
from pyfoods.app import create_app


@pytest.fixture
def app():
    return create_app()


@pytest.fixture
def client(app):
    with TestClient(app) as _client:
        yield _client


def test_api_version(client):
    """ Test API Version
    """
    response = client.get("/api/")
    assert response.status_code == 200
    assert response.json() == {"status": "Ok", "version": "1.0"}


def test_reservations_list(client):
    response = client.get("/api/reservations/")
    assert response.status_code == 200
    assert response.json() == []


def test_reservations_post_success(client):
    payload = {"foodtruck": "foodtruck1", "date": "2024-11-09T15:49:43.127Z"}
    response = client.post("/api/reservations/", json=payload)
    assert response.status_code == 200
    assert response.json() == {"foodtruck": "FOODTRUCK1", "date": "2024-11-09T15:49:43.127000Z"}


def test_reservations_post_week_error(client):
    payload = {"foodtruck": "foodtruck1", "date": "2024-11-09T15:49:43.127Z"}
    response = client.post("/api/reservations/", json=payload)
    assert response.status_code == 400


def test_reservations_list_by_name_success(client):
    response = client.get("/api/reservations/foodtruck1")
    assert response.status_code == 200
    assert response.json() == [{"foodtruck": "FOODTRUCK1", "date": "2024-11-09T15:49:43.127000Z", "id": 1}]


def test_reservations_list_by_name_by_date_success(client):
    response = client.get("/api/reservations/foodtruck1/2024-11-09T00:00:00.000Z")
    assert response.status_code == 200
    assert response.json() == [{"foodtruck": "FOODTRUCK1", "date": "2024-11-09T15:49:43.127000Z", "id": 1}]


def test_reservations_delete(client):
    response = client.delete("/api/reservations/1")
    assert response.status_code == 200


## TODO
# test rules 1
# test rules 2
# test rules 3


