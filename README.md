# pingur

early version, for now only warnings for ssl certs that are going to expire
recomeneded use, is to use the docker image, and run it like this (using a file with domains in urls.yml):
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
