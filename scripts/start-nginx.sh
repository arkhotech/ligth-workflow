#!/bin/bash 

/get-params.sh $SSM_SCRIPT_PARAMS  

/get-params.sh /etc/nginx/conf.d/site.conf core_container_name core_frontend_name

#CONTAINER_NAME=$(aws ssm get-parameter --name core_container_name_${ENV_NAME} | jq -r '.Parameter.Value')
#FRONTEND_NAME=$(aws ssm get-parameter --name core_frontend_name_${ENV_NAME} | jq -r '.Parameter.Value')

#sed -i "s/core-api/$CONTAINER_NAME/g" /etc/nginx/conf.d/site.conf
#sed -i "s/backend.local/$FRONTEND_NAME/g" /etc/nginx/conf.d/site.conf

nginx -g "daemon off;"
