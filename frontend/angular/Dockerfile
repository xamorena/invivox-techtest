FROM node:22
LABEL org.opencontainers.image.authors="xavier.amorena@gmail.com"

WORKDIR /frontend
ADD ./*.json /frontend/
ADD ./*.md /frontend/
RUN npm install -g @angular/cli
RUN npm install
ADD ./public /frontend/public
ADD ./src /frontend/src
RUN npm run build

CMD ["ng", "serve", "--host", "0.0.0.0"]
