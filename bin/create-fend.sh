#!/bin/bash


docker run -d -p 80:80 \
       --network backend \
       --restart=unless-stopped \
       --name frontend --env-file env_file arkhhotech/lw-frontend
