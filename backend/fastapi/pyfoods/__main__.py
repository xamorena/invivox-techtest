"""
"""
import logging
import os

import uvicorn

from pyfoods.app import create_app


def main():
    """
    Application Server Main
    """
    exit_code = 0
    try:
        logging.basicConfig(
            level=logging.DEBUG if os.getenv("DEBUG") in ["1"] else logging.INFO
        )
        print("Press [CTRL-C] to quit, starting http server ...")
        app = create_app()
        uvicorn.run(
            app, host=os.getenv("HOST", "0.0.0.0"), port=int(os.getenv("PORT", "8000"))
        )
        print("Done")
    except (SystemExit, KeyboardInterrupt):
        pass
    except Exception as e:
        print(f"Error: {e}")
        exit_code = -1
    finally:
        print("bye")

    return exit_code


if __name__ == "__main__":
    exit(main())
