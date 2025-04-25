# Symfony File Transfer

A modern service for secure file transfer with advanced monitoring and management features.

## Key Features

-   🔒 Secure file transfer with encryption
-   📊 Detailed usage analytics
-   👥 Business account management (up to 20 users)
-   📈 Real-time transfer status monitoring
-   🎨 Branding customization for business accounts

## Tech Stack

### Backend
-   **Symfony 7.2** - modern PHP framework
-   **Doctrine ORM** - database interaction
-   **Services** - modular application architecture
-   **Fixtures & Factory** - test data generation
-   **PHP Enums** - strict data typing
-   **Symfony Turbo** - for creating an SPA-like application

### Frontend
-   **Stimulus.js** - for interactive components
-   **Turbo** - for navigation without page reloads
-   **Asset Mapper** - asset management

### Infrastructure
-   **Docker** - application containerization
-   **Makefile** - automation of development commands
-   **Nginx** - web server
-   **MySQL 8** - database
-   **Mailpit** - email handling

## Installation and Startup

1.  Clone the repository:
    ```bash
    git clone [https://github.com/your-username/symfony-file-transfer.git](https://github.com/your-username/symfony-file-transfer.git)
    cd symfony-file-transfer
    ```

2.  Start the project using Docker:
    ```bash
    docker compose up --build
    ```

3.  Install dependencies:
    ```bash
    composer install
    ```
    *(Note: Run this command on your host machine or inside the container if composer is not installed locally)*

4.  Apply database schema updates:
    ```bash
    make db-update
    ```
    *(Note: This uses `doctrine:schema:update --force`. If you prefer using migrations, set up Doctrine Migrations and use a `make migrate` command)*

5.  Load test data:
    ```bash
    make fixtures
    ```
    *(Note: This loads fixtures without purging the database by default)*

## Available Commands

-   `make bash` - enter the application container
-   `make console cmd=<command>` - execute a Symfony command
-   `make fixtures` - load fixtures (without interaction/confirmation)
-   `make fixtures-load` - load fixtures (with confirmation prompt, likely purges)
-   `make db-update` - update the database schema (using `doctrine:schema:update --force`)
-   `make cleanup-files` - cleanup expired files
-   `make fix` - apply php-cs-fixer
-   `make watch` - run CSS build and watch
-   `make show limits` - show file upload limits *(Note: The actual target name in the Makefile might be `show-limits` or `show_limits`)*
