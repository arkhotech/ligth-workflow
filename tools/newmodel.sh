#!/bin/bash

docker exec -ti ligth-workflow_core_1 php artisan make:model $1 -a
