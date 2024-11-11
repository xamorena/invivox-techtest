APP=techtest
BACKEND=fastapi  # fastapi, symfony

$(APP)-setup:
	echo Configuring $APP
	echo Installing frontend/Angular Javascript packages
	cd frontend/angular && npm install && cd ../..
	echo Installing backend/FastAPI Python packages
	cd backend/fastapi && ./setup.sh && cd ../..
	echo Installing backend/Symfony PHP packages
	cd backend/symfony && composer install && cd ../..

$(APP)-docker:
	echo Building FastAPI Docker image for $APP
	docker build . -f FastAPI-Dockerfile -t techtest-fastapi

$(APP)-symfony-docker:
	echo Building Symfony Docker image for $APP
	docker build . -f Symfony-Dockerfile -t techtest-symfony

$(APP)-start:
	echo Starting $APP on http://127.0.0.1:8000
	docker run -p 8000:80 techtest-fastapi
