APP=techtest
BACKEND=fastapi  # fastapi, symfony

$(APP)-setup:
	echo Configuring $APP
	echo Installing frontend/Angular packages
	cd frontend/angular && npm install && cd ../..
	echo Creating backend/FastAPI virtual environment
	cd backend/fastapi && ./setup.sh && cd ../..

$(APP)-build:
	echo Building $APP
	cd frontend/angular && npm build && cd ../..
	echo Copying frontend distribution to backend/SPA
	cp -fR frontend/angular/dist/frontend/browser/* backend/fastapi/pyfoods/spa/

$(APP)-tests:
	echo Testing $APP

$(APP)-image:
	echo Building Docker image $APP

$(APP)-start:
	echo Starting $APP
	cd backend/fastapi && . venv/bin/activate && python3 -m prisma db push && HOST="0.0.0.0" PORT=8000 python3 -m pyfoods
