On this day ... on Pinboard.in
==============================

This web app displays bookmarks from your pinboard.in account that you posted one, two, three or more years ago -
exactly on this day.

At the moment users have to be added to the database manually.


## Installation
Download and unzip archive from GitHub, go to folder and run

    composer install

Create a database (MySQL, Postgres, SQLite, etc).

Copy the file env.sample to .env and input the config data. Alternatively, set the environment variables in
your web server configuration.

Initialize the database:

    ./console dbinit

Import your data on the console and create your first user:

    ./console import:all -u your_pinboard_username -a your_pinboard_api_key

To run it with the builtin PHP web server on Port 8082:

    ./runserver.sh

## Deployment with [Ansible](http://www.ansible.com)

1. Fork the repository.
2. Log in to your deployment host with SSH and add the SSH public key of the _deployment host_ to your deployment keys on GitHub. 
3. Add the GitHub host to .ssh/known_hosts.
4. Log out from deployment host
5. create a file `deployment/inventory` (see below for example).
6. Edit the project root and repository settings in `deployment/vars/main.yml` 
7. Call `ansible-playbook  -i deployment/inventory deployment/deployment.yml`


Example `deployment/inventory` file:
```
[web]
your.host.name.com 	ansible_ssh_user=your_ssh_username_on_host
```

## TODO
- Update Bootstrap and add local fallback for Bootstrap CDN.
- Pagination instead of cutoff after 100 bookmarks
- Tag cloud for each day, to see what was interesting on that day.
- Delete, edit and "mark as unread" buttons - Can be implemented separately. Delete and unread buttons are easy, edit needs a form functionality that reproduces the one form pinboard.in.
- OAuth login (Facebook, twitter, github) and user profiles
- Settings page (for setting ascending/descending order, max. number of Bookmarks per year, display private bookmarks)
- RSS feed with HTTP auth
- Denormalization of bookmark date in DB for better indexing and more speed.
- Allow emoji (utf8mb4) in database (Import generates error on emoji)

