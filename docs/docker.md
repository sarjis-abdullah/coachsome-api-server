# Docker
Here we describe the docker setup.

## TOC
1. [CMD](#CMD)

## CMD
Build the app image with the following command:

```shell
docker-compose build app
```
When the build is finished, you can run the environment in background mode with:

```shell
docker-compose up -d
```
To show information about the state of your active services, run
```shell
docker-compose ps
```
We’ll now run composer install to install the application dependencies:
```shell
docker-compose exec app composer install
```
To shut down your Docker Compose environment and remove all of its containers, networks, and volumes, run:
```shell
docker-compose down
```

