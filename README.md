JivyDocs
=======

Allows the javadocs stored inside of an Ivy repository to be served over http.

It provides an index of modules that contain javadocs.  The first time the url
for a particular revision's javadocs is hit, it will extract the javadoc jar
into the cache directory.  From then on, it will serve the javadocs out of the
cache as static files.

Requirements
------------

* Ivy repository
* Apache server with PHP and `mod_rewrite`

Installation
------------

`unpack.php` and `.htaccess` must be placed in the root of the site.

Configuration
-------------

There are two variables to configure at the top of `unpack.php`: `$archive_root`
and `$cache_root`

### Archive Root
The archive root is the path of the Ivy repository.  It must be a filesystem
repository in a path readable by the web server.  The structure of the
repository should be
`{repository root}\{organisation}\{module}\{revision}\javadocs\{module}.jar`.

### Cache Directory
The cache root is the path of the directory that the jar files will be
extracted to, and the static documentation served from.  It must be readable
and writable by `www-data`.

Warning
-------

Use this software at your own risk. It is designed for use in an environment
with trusted people, so security has not been a major concern in design or
testing. 
