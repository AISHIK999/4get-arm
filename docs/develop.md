# Develop

Start the PHP webserver like this, assuming you have curl-impersonate installed already

```sh
LD_PRELOAD=/usr/local/lib/libcurl-impersonate-ff.so CURL_IMPERSONATE=ff117 php -S localhost:8000
```

# Debug guide for docker

Install:
```
git clone git.lolcat.ca/lolcat/4get
make changes to docker/apache/http/httpd.conf (comment out the lines that disable logging)
docker build -t luuul/4get .
```

Run:
```
docker run --name 4get -p 80:80 -p 443:443 luuul/4get 
```

Get debug logs:
```
docker exec -it `docker ps -qf ancestor=luuul/4get:latest` sh # browse the container
docker exec -it `docker ps -qf ancestor=luuul/4get:latest` tail -f /var/log/apache2/error_log
```

need to restart container?
```
docker restart 4get
```
