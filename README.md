On this day ... on Pinboard.in
==============================

This web app displays bookmarks from your pinboard.in account that you posted one, two, three or more years ago -
exactly on this day.

At the moment users have to be added to the database manually.


## Installation
Download and unzip archive from Github, go to folder and run

    composer install

Create a database (MySQL, Postgres, SQLite, etc), copy the file env.sample to .env and input the config data.

Initialize the database:

    ./console dbinit

Import your data with the console

    ./console import:all -u your_pinboard_username -a your_pinboard_api_key

To run it with the builtin PHP web server on Port 8082:

    ./runserver.sh

## TODO
- RSS Feed
- OAuth login (Facebook, twitter, github) and user profiles
