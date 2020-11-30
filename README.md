# grids

### Overview

This is demo project I use to prototype jQuery grids.  It uses minimal Symfony components and other packages such as the following:

- symfony/dotenv
- monolog/monolog
- tracy/tracy
- fabpot/goutte
- twig/twig


Part of the Gijgo jQuery controls released under MIT license. <https://gijgo.com/grid>

### Code of Interest

- bootstrap.php  sets up app environment 
- init/crawler.php  dom crawler to parse Gijgo's website for control data that's inserted into a SQLITE3 DB
- index.php that sets up the context from the grid twig templates
- templates/grids/basic/*
    - table.html.twig
        - grid.html.twig
            - base/grid_base.html.twig
