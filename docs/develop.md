# Develop

Start the PHP webserver like this, assuming you have curl-impersonate installed already

```sh
LD_PRELOAD=/usr/local/lib/libcurl-impersonate-ff.so CURL_IMPERSONATE=ff117 php -S localhost:8000
```
