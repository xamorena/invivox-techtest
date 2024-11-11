# Invivox Test Technique

## Enoncé du test 

Pied piper est une société avec 500 salariés.
Afin d'agrémenter leur déjeuner, elle met à disposition des emplacements pour les foodtrucks. 

Quelques règles ont été mises en place pour assurer un roulement.
Du lundi au jeudi, huit emplacements sont mis à disposition. Sept le vendredi. 
Chaque foodtruck ne peut réserver qu'un emplacement par semaine.

Dans le but d'intégrer ces réservations au SIRH, Pied piper a besoin d'une API avec les endpoints suivants :

- Ajout d'une réservation avec une date, un nom
- Suppression d'une réservatio
- Liste des réservations par jour. 

_Étonnamment, c'est le nom du foodtruck qui sert de clé d'unicité, malgré les réserves émises.
M. Hendricks (CTO) semble s'en contenter._

## Réalisation

### Definiton des routes de l'API

| Methode | Route                                                   | Description                                   |
|:-------:|:--------------------------------------------------------|:----------------------------------------------|
|  POST   | /api/reservations/                                      | Ajout d'une réservation avec une date, un nom | 
| DELETE  | /api/reservations/{reservation_id}                      | Suppression d'une réservation                 |
|   GET   | /api/reservations/                                      | Liste des réservations                        |
|   GET   | /api/reservations/{reservation_name}                    | Liste des réservations par nom.               |
|   GET   | /api/reservations/{reservation_name}/{reservation_date} | Liste des réservations par jour.              |

### Modèles

#### Réservations

| Nom       | Type      | Description         |
|:----------|:----------|:--------------------|
| id        | int       | Indentifiant unique |
| foodtruck | char[256] | Nom du foodtruck    |
| date      | datetime  | Date de reservation |

### Applications

#### API

Deux implementations sont proposées:
- Python/FastAPI, BACKEND=fastapi : backend/fastapi
- PHP/Symfony, BACKEND=symfony : backend/symfony

La base de données est configurable vie la variable d'environnemrnt DATABASE_URL.

_Pour la demo, la base de données est stockés en **SQLite** dans les repertoire /data/reservations.db_

#### GUI

- Typescript/Angular, FRONEND=angular : frontend/angular

### Configutation

#### Frontend/Angular

```bash
cd frontend/angular
npm install
npm run build
```

```bash
npm serve
```

#### Backend/FastAPI

##### Configuration

```bash
cd backend/fastapi
python3 -m venv venv
source venv/bin/activate
python3 -m pip install -r requirements.txt
python3 -m prisma db push
```
##### Lancement

```bash
cd backend/fastapi
source venv/bin/activate
python3 -m pyfoods 
```

##### Test

```bash
cd backend/fastapi
source venv/bin/activate
rm data/reservations.db
python3 -m prisma db push
PYTHONPATH=. pytest tests
```

### Docker Image

```bash
docker build . -f FastAPI-Dockerfile -t techtest-fastapi
```

#### Docker

```bash
docker run -p 8000:80 techtest-fastapi
```

### Liens

- GUI : [http://localhost:8000/](http://localhost:8000/)
- API : [http://localhost:8000/api](http://localhost:8000/api)
- OpenAPI/Swagger : [http://localhost:8000/api/doc](http://localhost:8000/api/doc)


_le volume **/opt/backend/data** peut être monté sur l'hote pour acceder au données du container_

## Annexes

La liste des emplacements est configurable via un fichier YAML:
- [/data/locations.yaml](./backend/fastapi/data/locations.yaml)

```yaml
- address: Address 1
  label: Spot 1
```

La liste des contraintes de resevations par semaine est configurable via un fichier YAML:
- [/data/week_limits.yaml](./backend/fastapi/data/week_limits.yaml)

```yaml
# Lundi
0: [1, 2, 3, 4, 5, 6, 7, 8]
# Mardi
1: [1, 2, 3, 4, 5, 6, 7, 8]
# Mercredi
2: [1, 2, 3, 4, 5, 6, 7, 8]
# Jeudi
3: [1, 2, 3, 4, 5, 6, 7, 8]
# Vendredi
4: [1, 2, 3, 4, 5, 6, 7]
# Samedi
5: []
# Dimanche
6: []
```
