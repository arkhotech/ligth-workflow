#!/bin/bash


docker run -d -p 3306:3306 --name lw_database --network backend --env-file  mysql_vars -v database:/var/lib/mysql mysql:5.7.22
