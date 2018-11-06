#!/bin/bash

if [ $# -lt 2 ]; then
  echo "Uso:  $0 [archivo] [parametros]+"
  exit 1
fi

#Chequer si el archivo existe

if [ ! -f "$1" ]; then
	echo "El archivo no existe"
	exit 1
fi

FILE=$1
PARAMS=$2

#Checkerar que las variables que indican el ambiente en el que se encuentran son validas
if [ -z "${ENV_NAME}" ]; then
   echo "No se ha establecido la variable de ambiente AMBIENTE"
   exit 1
fi

while [ ! -z $2 ]
do
   echo $2
   PARAM=$2
   echo "Recuperando parametro ${PARAM}_${ENV_NAME}"
   VALUE=$(aws ssm get-parameter --name ${PARAM}_${ENV_NAME} | jq -r '.Parameter.Value')   
   if [ ! -z "$VALUE" ];
   then 
      echo "reemplazando valor en archivo: ${FILE}"
      sed -i "s/#{$PARAM}/${VALUE}/g" ${FILE} 
   else
	echo "Error, el parametro ${PARAM}_${ENV_NAME} no existe"
        exit 1
   fi
   shift
done

chown www-data:www-data ${FILE}
