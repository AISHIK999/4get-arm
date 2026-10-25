# Sample Apache2 configuration
This is the apache2 configuration file used on the 4get.ca official instance, in hopes that it's useful to you!

Apache2 now sits behind haproxy, so it's configured to accept plain HTTP traffic on port `8080`. I highly recommend you setup haproxy, since apache2 is pretty slow at handling SSL/HTTP2. A sample haproxy config has been added at the end of this document as a reference.

Looking for the apache2 guide? <a href="https://git.lolcat.ca/lolcat/4get/src/branch/master/docs/apache2.md">go here.</a>.

```xml
<VirtualHost *:8080>
	ServerName 4get.ca
	ServerAlias 4getwebfrq5zr4sxugk6htxvawqehxtdgjrbcn2oslllcol2vepa23yd.onion

	# compress
	AddOutputFilterByType DEFLATE application/json
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE text/css
	
	DocumentRoot /var/www/4get
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog /dev/null combined
	#CustomLog /var/log/apache2/access.log combined
	
	<FilesMatch \.php$>
		SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost/"
	</FilesMatch>

	<Directory /var/www/4get>
		Options -MultiViews
		AllowOverride All
		Require all granted
		
		# prevent 404s from hitting php-fpm
		RewriteEngine On
		RewriteCond %{REQUEST_URI} \.php$ [NC]
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^ - [R=404,L]
		
		# prevent /index hits
		RewriteRule ^index$ - [R=404,L]
		
		# 404 direct .php file access
		RewriteCond %{THE_REQUEST} "\s/+[^?\s]*\.php(?:/|[?\s])" [NC]
		RewriteRule ^ - [R=404,L]
		
		# internally redirect to non-php extension
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME}.php -f
		RewriteRule ^([^\.]+)$ $1.php [NC,L]
	</Directory>

	# deny access to private resources
	<DirectoryMatch "^/var/www/4get/(data|lib|scraper|template|oracles|docs|docker|extra|resolve)/">
		Require all denied
	</DirectoryMatch>
	
	<Directory /var/www/4get/icons>
		Options -Indexes
	</Directory>
</VirtualHost>
```

# Haproxy configuration

```cfg
global
	#log /dev/log	local0
	log /dev/log	local0 warning
	log /dev/log	local1 notice
	chroot /var/lib/haproxy
	stats socket /run/haproxy/admin.sock mode 660 level admin
	stats timeout 30s
	user haproxy
	group haproxy
	daemon

	# Default SSL material locations
	ca-base /etc/ssl/certs
	crt-base /etc/ssl/private

	# See: https://ssl-config.mozilla.org/#server=haproxy&server-version=2.0.3&config=intermediate
	#ssl-default-bind-ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
	ssl-default-bind-ciphers HIGH:!aNULL
	ssl-default-bind-ciphersuites TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256
	ssl-default-bind-options ssl-min-ver TLSv1.2 no-tls-tickets
	
	# ja4 settings
	tune.lua.bool-sample-conversion normal
	tune.ssl.capture-buffer-size 190
	lua-load /etc/haproxy/lua/ja4.lua
defaults
	log	global
	mode	http
	#option	httplog
	option	dontlognull
	timeout connect 5000
	timeout client  50000
	timeout server  50000
	errorfile 400 /etc/haproxy/errors/400.http
	errorfile 403 /etc/haproxy/errors/403.http
	errorfile 408 /etc/haproxy/errors/408.http
	errorfile 500 /etc/haproxy/errors/500.http
	errorfile 502 /etc/haproxy/errors/502.http
	errorfile 503 /etc/haproxy/errors/503.http
	errorfile 504 /etc/haproxy/errors/504.http

frontend http_80_in
	# bind tcp ip4/6
	bind *:80
	bind [::]:80
	
	mode http
	
	# handle ip.lolcat.ca+onion addy on plain HTTP
	acl is_ip_host hdr(host) -i ip.lolcat.ca ip4.lolcat.ca ip6.lolcat.ca 4getwebfrq5zr4sxugk6htxvawqehxtdgjrbcn2oslllcol2vepa23yd.onion
		
	# redirect to https
	http-request redirect scheme https code 301 if { hdr(host) -i lolcat.ca }
	http-request redirect scheme https code 301 if { hdr(host) -i git.lolcat.ca }
	http-request redirect scheme https code 301 if { hdr(host) -i lick.lolcat.ca }
	http-request redirect scheme https code 301 if { hdr(host) -i cf-tls.lolcat.ca }
	http-request redirect scheme https code 301 if { hdr(host) -i cf-js.lolcat.ca }
	http-request redirect scheme https code 301 if { hdr(host) -i cf-captcha.lolcat.ca }
	
	# redirect www.lolcat.ca to lolcat.ca
	http-request redirect location https://lolcat.ca%[capture.req.uri] code 301 if { hdr(host) -i www.lolcat.ca }
	
	# everything else redirects to 4get.ca
	http-request redirect location https://4get.ca%[capture.req.uri] code 301 unless is_ip_host
	
	# ... unless its an ip host, or the onion domain
	use_backend apache if is_ip_host

frontend https_443_in
	# bind tcp ip4/6
	bind *:443 ssl crt /etc/apache2/ssl/4get-haproxy.pem crt /etc/apache2/ssl/lolcat-haproxy.pem
	bind [::]:443 ssl crt /etc/apache2/ssl/4get-haproxy.pem crt /etc/apache2/ssl/lolcat-haproxy.pem
	
	# QUIC is disabled. See https://github.com/haproxy/haproxy/issues/3529
	# bind udp ip4/6
	#bind quic4@*:443 ssl crt /etc/apache2/ssl/4get-haproxy.pem crt /etc/apache2/ssl/lolcat-haproxy.pem alpn h3
	#bind quic6@[::]:443 ssl crt /etc/apache2/ssl/4get-haproxy.pem crt /etc/apache2/ssl/lolcat-haproxy.pem alpn h3
	
	# tell client we support http3 now
	#http-response set-header Alt-Svc "h3=\":443\"; ma=86400"
	
	mode http
	
	# redirect all unknown domains to 4get.ca
	acl valid_https_hosts hdr(host) -i 4get.ca lolcat.ca ip.lolcat.ca ip4.lolcat.ca ip6.lolcat.ca git.lolcat.ca lick.lolcat.ca cf-tls.lolcat.ca cf-js.lolcat.ca cf-captcha.lolcat.ca www.lolcat.ca
	
	# add onion domain to 4get.ca
	acl is_4get hdr(host) -i 4get.ca
	http-request set-var(txn.is_4get) bool(true) if is_4get
	http-response set-header Onion-Location http://4getwebfrq5zr4sxugk6htxvawqehxtdgjrbcn2oslllcol2vepa23yd.onion%[capture.req.uri] if { var(txn.is_4get) -m bool }

	# git.lolcat.ca -> gitea service
	acl acl_gitea hdr(host) -i git.lolcat.ca
	
	# redirects
	http-request redirect location https://4get.ca%[capture.req.uri] code 301 unless valid_https_hosts
	http-request redirect location https://lolcat.ca%[capture.req.uri] code 301 if { hdr(host) -i www.lolcat.ca }
	
	# add security headers
	http-response set-header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
	http-response set-header X-Content-Type-Options "nosniff"
	http-response set-header X-Frame-Options "SAMEORIGIN"
	
	# route
	use_backend gitea if acl_gitea
	default_backend apache

backend apache
	# pass ja4
	http-request lua.fingerprint_ja4 if { ssl_fc }
	http-request set-header X-Ja4 %[var(txn.fingerprint_ja4)] if { ssl_fc }
	
	# delete server header
	http-response del-header Server
	
	# pass remote IP
	option forwardfor
	server apache_server 127.0.0.1:8080 check

backend gitea
	server gitea_server 127.0.0.1:3000 check
```
