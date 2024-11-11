FROM python:3.12-slim
LABEL org.opencontainers.image.authors="xavier.amorena@gmail.com"

WORKDIR /backend
RUN apt update \
    && apt install -y python3-venv \
    && apt clean \
    && rm -rf /var/lib/apt/lists /var/cache/apt
RUN python3 -m venv venv
RUN . venv/bin/activate
ADD ./data /backend/data/
ADD ./requirements.txt /backend/requirements.txt
RUN python3 -m pip install --upgrade pip
RUN python3 -m pip install -r requirements.txt
ADD ./migrations /backend/migrations
ADD ./pyfoods /backend/pyfoods
ADD ./.env /backend/.env
ADD ./schema.prisma /backend/schema.prisma
RUN python3 -m prisma db push

CMD ["python3", "-m", "pyfoods"]