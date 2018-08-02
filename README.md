# pingur

Recomended use, is to use the docker image https://hub.docker.com/r/digitalist/pingur.io/, and run it like this (using a file with domains in urls.yml, and config in config.yml):
```
docker run -v $(pwd)/proddata/config.yml:/opt/pingur/.pingur/config.yml -v $(pwd)/urls.yml:/opt/pingur/urls.yml digitalist/pingur.io  pingur run:checks --file=urls.yml
```
Example config.yml:

```
---
# config for pingur
slack:
  endpoint: https://hooks.slack.com/services/JHGJHGJHGT54667HJK
cert:
  warning: 10 #how many days before expiration pingur should warn
```

Example urls.yml

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

For just running the cert check for one site:
```
docker run digitalist/pingur.io  pingur c:c --domain=motherjones.com
```

## Influxdb
pingur has support for using influxdb as a db backend for the results, just add url, port and db name in config.yml, like:

```
influxdb:
  url: influxdbdomain
  port: 8086
  db: pingur

```
