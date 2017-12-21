# pingur

early version, for now only warnings for ssl certs that are going to expire

to setup pingur, upload a phar (best build with box - build with `./box -v`).

create a .pingur folder, add a file called config.yml
in that file add endpoint for slack and how many days before notices should be sent to slack for expiring certs.

Like this:

```
---
# config for pingur
slack:
  endpoint: https://hooks.slack.com/services/JHGJHGJHGT54667HJK
cert:
  warning: 10 #how many days before expiration pingur should warn

setting up urls to check:

create a yml file, example urls.yml:

```
mysite.com:
  https: true

myothersite.com
  https: true

```

provide that file as a parameter to pingur for the run:checks command. like:


``` 
pingur run:checks --file=urls.yml
```

pingur should be runned from the path that has the .pingur folder, if the urls.yml 
is in the same path just use the filename, otherwise provide absolute path
