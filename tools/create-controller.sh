#!/bin/bash

docker exec -ti ligth-workflow_core_1 php artisan make:controller $1 --invokable
