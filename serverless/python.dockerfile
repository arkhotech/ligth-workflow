FROM ubuntu

RUN apt-get update;

RUN apt-get install -y python

RUN apt install -y python-pip

RUN apt install -y default-libmysqlclient-dev python-dev

RUN useradd -d /app -m engine



#Instalacion de librerías para Python

RUN pip install MySQL-python ;\
    pip install flask ;\
    pip install Django ; \
    pip install djangorestframework 

USER engine

WORKDIR /app

CMD python main.py
