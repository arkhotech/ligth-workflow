# ligth-workflow
Proyecto para Workflow con interfaz Web basado en Simple


```
  docker exec -ti ligth-workflow_core_1 composer require laravel/passport
  docker exec -ti ligth-workflow_core_1 php artisan migrate
  docker exec -ti ligth-workflow_core_1 php artisan migrate
  docker exec -ti ligth-workflow_core_1 php artisan passport:install
  docker exec -ti ligth-workflow_core_1 php artisan make:middleware CheckApiToken
  docker exec -ti ligth-workflow_core_1 php artisan route:list
```

Client ID para generar token

```bash
docker exec -ti ligth-workflow_core_1 php artisan passport:client --personal
```

Motor de reglas:

https://github.com/hoaproject/Ruler

https://voltagead.com/the-basics-of-laravel-queues-using-redis-and-horizon/
