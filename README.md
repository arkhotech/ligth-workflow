# ligth-workflow
Proyecto para Workflow con interfaz Web basado en Simple



# Ambiente Docker

El ambiente docker, esta compuesto por un stack  5 contenedores:

* Mysql como base de datos
* Redis para el control de colas y cache 
* php fpm 7 para el motor en si
* php fpm 7 para el worker y control de colas
* nginx como capa frontend del stack

Para iniciar el stack con composer:
(debe estar en la misma carpeta el archivo docler-compose.yml, esta en la raíz del proyecto)
```
docker-compose up -d
```

Para detener el container:

```
docker-compose down
```

# Iniciar requisitos

Antes de comenzar, se debe hacer el setup del ambiente. Como este ambiente esta desarrollado sobre docker,
se necesita ejecutar los comandos composer y artisan sobre el contenedor "_core_", normalmente con el nombre: ligth-workflow_core_1

## Ejecución de comandos en Docker

Para el desarrollo de aplicaciones y el ambiente docker, ejecutar los comandos de la manera siguiente:

_docker exec -ti ligth-workflow_core_1 COMANDO_

ej.Para ejecutar la migración:
```
docker exec -ti ligth-workflow_core_1 php artisan migrate
```



# Seguridad

La seguridad hasta el momento se basa en Oauth2 con Laravel passport.

Seguramente al iniciar la aplicación los requisitos


```
  docker exec -ti ligth-workflow_core_1 composer require laravel/passport
  docker exec -ti ligth-workflow_core_1 php artisan migrate
  docker exec -ti ligth-workflow_core_1 php artisan passport:install
  docker exec -ti ligth-workflow_core_1 php artisan make:middleware CheckApiToken
  docker exec -ti ligth-workflow_core_1 php artisan route:list
```

Client ID para generar token

```bash
docker exec -ti ligth-workflow_core_1 php artisan passport:client --personal
```


# Desarrollo

Para el desarrollo de aplicaciones y el ambiente docker, ejecutar los comandos de la manera siguiente:

docker exec -ti ligth-workflow_core_1 COMANDO

ej.Para ejecutar la migración:
```
docker exec -ti ligth-workflow_core_1 php artisan migrate
```

Para ejecutar composer:

```
docker exec -ti ligth-workflow_core_1 php artisan migrate
```


Motor de reglas:

https://github.com/hoaproject/Ruler

https://voltagead.com/the-basics-of-laravel-queues-using-redis-and-horizon/

Mathematical Library

https://github.com/markrogoyski/math-php


