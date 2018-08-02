# pingur

early version, for now only warnings for ssl certs that are going to expire

to setup pingur, upload a phar (best build with box - build with `./box build -v`).

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
```

setting up urls to check:

create a yml file, example urls.yml:
```
mysite.com:

myothersite.com
  https: false
  needle: 'bar' # text to check for in response in url request
  url: 'foo/' # without slash in beginning.

myforgottensite.com
  needle: 'bar' # text to check for in response in url request
  url: 'foo/' # without slash in beginning.

```

provide that file as a parameter to pingur for the run:checks command. like:


``` 
pingur run:checks --file=urls.yml
```

pingur should be runned from the path that has the .pingur folder, if the urls.yml 
is in the same path just use the filename, otherwise provide absolute path


# Docker
To run pingur as docker container:
docker run -v $(pwd)/proddata/config.yml:/opt/pingur/.pingur/config.yml -v $(pwd)/urls.yml:/opt/pingur/urls.yml pingur.io  pingur run:checks --file=urls.yml

You need to add volumes for the config.yml adn urls.yml
