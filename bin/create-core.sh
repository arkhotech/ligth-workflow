#!/bin/bash


docker run -d --name core \
       --network=backend \
       --env-file env_file \
       arkhotech/ligth-workflow:core


