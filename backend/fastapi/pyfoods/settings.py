import os

import yaml

DEFAULT_DATA_DIR = os.path.join(os.path.dirname(__file__), "..", "data")


class Config:
    SECRET_KEY = os.getenv("SECRET_KEY", "4fef8e33c8644b698439ab433637e940")
    CORS_ALLOW_ORIGN = os.getenv(
        "CORS_ALLOW_ORIGN", r"^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$"
    )
    DATA_DIR = os.getenv("DATA_DIR", DEFAULT_DATA_DIR)
    LOCATIONS_YAML = os.getenv(
        "LOCATIONS_YAML", os.path.join(DEFAULT_DATA_DIR, "locations.yaml")
    )
    LOCATIONS = [
        {"label": "Spot 1", "address": "Addres 1"},
        {"label": "Spot 2", "address": "Addres 2"},
        {"label": "Spot 3", "address": "Addres 3"},
        {"label": "Spot 4", "address": "Addres 4"},
        {"label": "Spot 5", "address": "Addres 5"},
        {"label": "Spot 6", "address": "Addres 6"},
        {"label": "Spot 7", "address": "Addres 7"},
        {"label": "Spot 8", "address": "Addres 8"},
        {"label": "Spot 9", "address": "Addres 9"},
    ]
    WEEK_LIMITS_YAML = os.getenv(
        "WEEK_LIMITS_YAML", os.path.join(DEFAULT_DATA_DIR, "week_limits.yaml")
    )
    WEEK_LIMITS = {
        # Lundi
        0: [1, 2, 3, 4, 5, 6, 7, 8],
        # Mardi
        1: [1, 2, 3, 4, 5, 6, 7, 8],
        # Mercredi
        2: [1, 2, 3, 4, 5, 6, 7, 8],
        # Jeudi
        3: [1, 2, 3, 4, 5, 6, 7, 8],
        # Vendredi
        4: [1, 2, 3, 4, 5, 6, 7],
        # Samedi
        5: [],
        # Dimanche
        6: [],
    }

    def __init__(self):
        if os.path.exists(Config.LOCATIONS_YAML):
            Config.LOCATIONS = self.load_yaml_file(Config.LOCATIONS_YAML)
        if os.path.exists(Config.LOCATIONS_YAML):
            Config.WEEK_LIMITS = self.load_yaml_file(Config.WEEK_LIMITS_YAML)

    @staticmethod
    def load_yaml_file(yaml_path: str):
        if os.path.exists(yaml_path):
            with open(yaml_path, mode="r") as f:
                return yaml.safe_load(f)


config = Config()
