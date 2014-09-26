JanusVR Mapper
==============

A headless client for the JanusVR server that scrapes a list of URLs from a given JanusVR compatible room and follows them a specfic number of levels deep to construct a map. User data is requested from the JanusVR server at regular intervals so it can show the population of each room. This data is then cached for a specific length of time and processed into a JanusVR compatible "Map Room" which users can access to see who's online.

It uses the [Laravel](http://laravel.com/) 4 PHP framework and is designed to run on a Linux server running Apache.

Installation
------------

1) The following command from the root of the repository: `php composer.phar install`. This will install any required composer packages into the `vendor` folder.

2) If developing locally you may want to set up a new folder with custom config for your environment in the `app/config` folder. Alternately, adjust the config in the `app/config/local` folder. 

3) You will need to edit `app/bootstrap/start.php` and set the `local` environment to match your development machine's hostname. Add other environment -> hostname mappings here as needed. You can find your hostname by opening up a terminal and running `hostname`.

4) You can also override only parts of the global config by specifying the keys you wish to override in a php file of the same name inside the `local` folder. I.e. you can add `debug => true` to a returned array in `app/config/local/app.php` to turn on debug mode for your local development server.