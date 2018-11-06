#!/bin/bash

function add_parameter () {
    echo "Cargando parametro: $1"
    aws ssm put-parameter --overwrite --name "$1" --type String --value "$2"
}

# DB_HOST=#{db_host}
# DB_PORT=#{db_port}
# DB_DATABASE=#{db_name}
# DB_USERNAME=#{db_user}
# DB_PASSWORD=#{db_pass}

add_parameter db_host_dev lw_database

add_parameter db_name_dev engine

add_parameter db_port_dev 3306

add_parameter db_user_dev  workflow

add_parameter db_pass_dev workflow123

add_parameter core_container_name_dev core

add_parameter core_frontend_name_dev backend.local


