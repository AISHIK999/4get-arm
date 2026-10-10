# Develop

Start the PHP webserver like this, assuming you have curl-impersonate installed already

```sh
LD_PRELOAD=/usr/local/lib/libcurl-impersonate-ff.so CURL_IMPERSONATE=ff117 php -S localhost:8000
```

Make sure to add `.php` to paths, the embedded PHP webserver doesnt support `mod_rewrite`-like directives.

If, for some reason, you're in an environment where accessing the debug logs is impossible (like those shitty shared webhosts), you can set `DISPLAY_ERRORS` to `true` in `data/config.php`.

# Debug guide for docker

Install:
```sh
git clone https://git.lolcat.ca/lolcat/4get
# make changes to docker/apache/http/httpd.conf (comment out the lines that disable logging)
docker build -t luuul/4get .
```

Run:
```sh
docker run --name 4get -p 80:80 -p 443:443 luuul/4get 
```

Get debug logs:
```sh
docker exec -it `docker ps -qf ancestor=luuul/4get:latest` sh # browse the container
docker exec -it `docker ps -qf ancestor=luuul/4get:latest` tail -f /var/log/apache2/error_log
```

Restart the container:
```sh
docker restart 4get
```
