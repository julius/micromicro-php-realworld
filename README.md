# ![RealWorld Example App](realworld_logo_for_readme.jpg)

> ### **_micro_micro.php** codebase containing real world examples that adheres to the [RealWorld](https://github.com/gothinkster/realworld) spec. This implements the frontend and the backend.


### [Demo](http://micromicro-php-realworld.atwebpages.com)&nbsp;&nbsp;&nbsp;&nbsp;[RealWorld](https://github.com/gothinkster/realworld)


This codebase was created to demonstrate a fully fledged fullstack application built with **_micro_micro.php** including CRUD operations, authentication, routing, pagination, and more.

For more information on how to this works with other frontends/backends, head over to the [RealWorld](https://github.com/gothinkster/realworld) repo.

## _micro_micro.php

The _micro_micro.php library is mostly just a config file to use Vanilla PHP.

But also an architecture and best practices catalogue based around the ideas of
- Page based architecture (instead of MVC)
- Avoiding Code Reuse as much as possible (DRY Anti-Pattern)
- Mature and stable technology
- Build fast, maintain fast
- No fat JS (SPAs cost too much + Bug Isolation)
- Stateless architecture (Fast to understand + Bug Isolation)

(TODO Link to details when they are ready)

Use PHP+Apache+MySQL without any frameworks:
- Page based architecture since the 90s
- Build-in, mature functionality for most web use-cases
- No dependency hell (libraries are very rarely needed)
- Advanced Routing (via .htaccess)
- Cheap and easy hosting
- Mature: Google "php \<do something\>"

## Install and Run (docker)
If you have docker and docker-compose installed. This will launch Apache + PHP + Mysql + PhpMyAdmin.
```bash
git clone git@github.com:julius/micromicro-php-realworld-example.git
cd micromicro-php-realworld-example

sudo docker-compose up -d
```

Open http://localhost:8080 in your browser. PhpMyAdmin (to manage the MySQL Database) runs at http://localhost:8081


## Install and Run (Cheap Hosting)
There is lots of cheap hosting for PHP+MySQL, often even for free.

- Upload all files (usually via FTP)
- Run `schema.sql` on the MySQL DB
- Put your MySQL Credentials into `_micro_micro.php`

## Install and Run (local apache)
You can, of course, install Apache with PHP and MySQL on your computer.
Google for `LAMP` (Linux) or `WAMP` (Windows).

- Serve this folder with Apache
- Make sure the rewrite module is active (to make `.htaccess` work)
- Create a MySQL DB
- Run `schema.sql` on the MySQL DB
- Put your MySQL Credentials into `_micro_micro.php`

