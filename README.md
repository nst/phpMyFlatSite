phpMyFlatSite
=============

phpMyFlatSite (MFS) is a minimal framework to run a classic, single user, dynamic web site.

I had been looking for such a framework for a long time.

Although there are plenty of them out there, I never found one that did suit my taste.

So I wrote mine in 2007. I was inspired by what I think is close to the perfect website : [exubero.com](http://exubero.com).

#### Features for Users

- install as simple as drag and drop
- runs anywhere where php4 is installed
- uses a flat file database, no DB engine required
- online pages edition
- a blog with rss feed and archives
- a contact page
- uses the [markdown syntax](http://daringfireball.net/projects/markdown/syntax)
- strict XHTML 1.0, CSS 2.0 compliant
- basic and simple template system
- really easy to tweak

#### Features for Programmers

- logic.php runs the whole site with only 450 lines of code, or 1000 words

#### Limitations

- can't create pages online
- can't upload files
- can't post comments

#### Live Demo

phpMyWebSite is running on [seriot.ch](http://www.seriot.ch).

#### Install

Put the `mfs` folder in your web server.

Point you web brower to `http://host/path_to_mfs/` and you're in.

Hint: you can log in by clicking the copyright name. Use `admin`/`password`.

You can change the default settings in `include/config.php`.

If necessary, allow the PHP process owner to write and edit files:

    $ chmod 777 blog
    $ chmod 646 blog/*.markdown
    $ chmod 646 texts/*.markdown
