Anm - advanced network monitoring
===============================

This is a php based web application which is used to monitor different network devices using SNMP protocol.

![Screenshot](http://anm.softhem.se/images/screenshot.png)

# [Demo](http://anm.softhem.se/)

INSTALLATIONS
---------------
  * Clone the repository `git clone git@bitbucket.org:iloveyii/anm.git`.
  * Create a database (manually for now) and adjust the database credentials in the `conf.php` file as per your environment.
  * Import the database dump file `mysql -uroot -p dbname < haae09_20130509.sql`.
  * Point web browser to index.php or Create a virtual host using [vh](https://github.com/iloveyii/vh) `vh new anm -p ~/anm/index.php`
  * Browse to [http://anm.loc](http://anm.loc) 
  


DIRECTORY STRUCTURE
-------------------

```
css                     contains css files
images                  contains images
js                      contains js files
php                     contains php code / classes
conf.php                contain configuration about database etc
haae09_20130509.sql     database dump file
README.md               Readme file
```