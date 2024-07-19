# Symfony framework starter template for the current LTS version

## Requirements
- **GNU Make:** *optional, enables some useful commands*
- **Docker**: *required*
- **Docker Compose:** *required*

## Setup
This steps will help you to setup the project for the first time.

### Step 1
Edit the following variable values in the **Makefile** so they fit your needs:
```
PROJECT_NAME := template-symfony
DOMAIN_BASE_URL := template-symfony.com
ECR_BASE_URL := 000000000000.dkr.ecr.us-east-1.amazonaws.com
```
***Note:** only the project's name is required for the project to run, domain base URL is used for SSL certificates generation and ECR base URL for pushing/pulling Docker images from AWS ECR.*

### Step 2
Run the following command from the **project's root**:
```
# dev env
make setup-dev

# prod env
make setup
```
***Note:** this will ask only for the **minimum required configuration**, build and run the Docker containers and setup required dependencies.*