Count release downloads on GitHub
=================================

This is a PHP script that calculates the number of downloads for each release in
a GitHub repository. It can either print a summary of the number of downloads
for each release, or generate an image with the total number of downloads that
you can include in your Readme file.

Usage
-----

The script can either be used from the command line, or on a web server with
PHP enabled.

### Command line

Run:

    $ php dlcount.php [options] user/repo

Where `user/repo` must be replaced with a valid GitHub username and repository
name (such as `sylvainhalle/textidote`).

Without any option, the output looks like this:

```
Release 0.2 18
  - somefile.zip 8
  - somefile.jar 10
Release 0.1 55
  - somefile.zip 31
  - somefile.jar 23
TOTAL: 73
```

For each release, you get the total number of downloads, and the number of
downloads for each file --as well as the grand total.

You can also generate an SVG badge (i.e. an image) that prints the total number
of downloads, using the `--image` switch. The syntax looks like this:

    $ php dlcount.php --image user/repo > myimage.svg

This will generate an SVG image called `myimage.svg` that will contain the text
"XXXX downloads".

### On a server

The script can also be placed on a web server (such as Apache) and be called
as an image URL. The syntax is as follows:

    https://example.com/path/to/dlcount.php?repo=user/repo

In this URL, replace `example.com` by the actual server name, `path/to` by the
path to the script on the server, and `user/repo` as before.

For this to work, you have to explicitly give the list of `user/repo`
combinations that are accepted; this is done by modifying the variable
`$REPO_WHITELIST` in the first lines of `dlcount.php`. The point of this feature
is to avoid other people using *your* server to generate badges for *their*
repositories.

Once this is done, you can use the URL in any HTML `img` tag.

### As a Cron job

If you put an `img` tag in your repository's `Readme`, the solution above will
send a request to your server every time the image URL is requested (in other
words, every time somebody loads the `Readme` page). This may be a bit overkill.
Alternately, you can setup your server to generate static image files and
refresh them periodically.

Let us assume you put `dlcount.php` in some repository (say `/opt`), and
that the root of your server is in `/var/www/html`. You can write a script
called `/opt/refresh-images.sh` that goes as follows:

```
#! /bin/bash
php /opt/dlcount.php --image user/repo1 > /var/www/html/repo1.svg
php /opt/dlcount.php --image user/repo2 > /var/www/html/repo2.svg
...
```

If you have multiple repositories, you can add multiple lines in this script.
When called, it will regenerate the download badges for each repository, and
put them in your server's root (`/var/www`).

Then, you can setup a Cron job to call this script periodically. Basically, you
can call:

    $ sudo cron -e

And then add a line to the file that looks like this:

```
00 * * * * /opt/refresh-images.sh
```

From then on, the script `refresh-images.sh` will be called every hour, and
regenerate the images in `/var/www`.

These static images can be referred to using a plain URL, such as
`http://example.com/repo1.svg`.

Customizing the image
---------------------

You can replace the SVG markup at the beginning of the script with the contents
of any other SVG file (such as one you make in
[Inkscape](https://inkscape.org)). Just make sure that the number of downloads
is the text "XXXX" (and ideally, is typeset in a monospace font).

About the author
----------------

Sylvain Hallé, Full Professor at
[Université du Québec à Chicoutimi](https://www.uqac.ca), Canada.

<!-- :maxLineLen=80: -->