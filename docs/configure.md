# 4get configuation options

Welcome! This guide assumes that you have a working 4get instance. This will help you configure your instance to the best it can be!

# Files location
1. The main configuration file is located at `data/config.php`
2. The proxies are located in `data/proxies/*.txt`
3. The captcha imagesets are located in `data/captcha/your_image_set/*.png`
4. The captcha font is located in `data/fonts/captcha.ttf`

# Cloudflare bypass (TLS check)
>These instructions have been updated to work with Debian 13 Trixie.

**Note: this only allows you to bypass the browser integrity checks. Captchas & javascript challenges will not be bypassed by this program!**

Configuring this lets you fetch images sitting behind Cloudflare and allows you to scrape the **Yep** search engine.

To come up with this set of instructions, I used [this guide](https://github.com/lwthiker/curl-impersonate/blob/main/INSTALL.md#native-build) as a reference, but trust me you probably want to stick to what's written on this page.

First, compile curl-impersonate (the firefox flavor).
```sh
git clone https://github.com/lwthiker/curl-impersonate/
cd curl-impersonate
sudo apt install build-essential pkg-config cmake ninja-build curl autoconf automake libtool python3-pip libnss3 libnss3-dev
mkdir build
cd build
../configure
make firefox-build
sudo make firefox-install
sudo ldconfig
```

Now, after compiling, you should have a `libcurl-impersonate-ff.so` sitting somewhere. Mine is located at `/usr/local/lib/libcurl-impersonate-ff.so`. Patch your PHP install so that it loads the right library:

```sh
sudo systemctl edit php8.4-fpm.service
```

^This will open a text editor. Add the following shit in there, in between those 2 comments I pasted for ya just for reference:

```sh
### Editing /etc/systemd/system/php8.4-fpm.service.d/override.conf
### Anything between here and the comment below will become the contents of the>

[Service]
Environment="LD_PRELOAD=/usr/local/lib/libcurl-impersonate-ff.so"
Environment="CURL_IMPERSONATE=firefox117"

### Edits below this comment will be discarded
```

Restart php8.4-fpm. (`sudo service php8.4-fpm restart`). To test things out, try making a search on "Yep", they check for SSL. If you get results (or a timeout, this piece of shit engine is slow as fuck) that means it works!

# Robots.txt
Make sure you configure this right to optimize your search engine presence! Head over to `/robots.txt` and change the 4get.ca domain to your own domain.

# Server listing
To be listed on https://4get.ca/instances , you must contact *any* of the people in the server list and ask them to add you to their list of instances in their configuration. The instance list is distributed, and I don't have control over it.

If you see spammy entries in your instances list, simply remove the instance from your list that pushes the offending entries.

# Proxies
4get supports rotating proxies for scrapers! Configuring one is really easy.

1. Head over to the **proxies** folder. Give it any name you want, like `myproxy`, but make sure it has the `txt` extension.
2. Add your proxies to the file. Examples:
	```conf
	# format -> <protocol>:<address>:<port>:<username>:<password>
	# protocol list:
	# raw_ip, http, https, socks4, socks5, socks4a, socks5_hostname
	socks5:1.1.1.1:juicy:cloaca00
	http:1.3.3.7::
	raw_ip::::
	```
3. Go to the **main configuration file**. Then, find which website you want to setup a proxy for.
4. Modify the value `false` with `"myproxy"`, with quotes included and the semicolon at the end.

Done! The scraper you chose should now be using the rotating proxies. When asking for the next page of results, it will use the same proxy to avoid detection!

## Important!
If you ever test out a `socks5` proxy locally on your machine and find out it works but doesn't on your server, try supplying the `socks5_hostname` protocol instead. Hopefully this tip can save you 3 hours of your life!

# 4play setup
4play is a Firefox extension I wrote that lets you control a browser from 4get. When you do a search, 4get sends a query to a nodeJS http server that communicates to the browser via websockets. The browser then reports back various payloads used for scraping. It's very similar to tools like puppeteer or playwright, except it uses websockets to communicate with the browser instead of the browser's debugger. This is needed to prevent `navigator.webdriver` from being set to `true` (also avoids a fuckton of other botchecks, that's just the tip of the iceberg).

## Requirements
- A dedicated laptop or computer running a full desktop environment (like Windows, Mac, or Linux DEs)
- Something to make the computer think it's connected to a screen. You need it, otherwise you'll fallback to software rendering and sites can detect that. You can use:
	- Your laptop's screen
	- A turned-on screen connected via HDMI
	- An EDID adapter to simulate a display (no affiliate links: [HDMI](https://www.aliexpress.com/item/1005011668390064.html), [DisplayPort](https://www.aliexpress.com/item/1005011659561729.html))
- Firefox ESR or the latest version of Firefox
- The [4play extension](https://addons.mozilla.org/en-US/firefox/addon/4play/) installed

## Installation
Install the dependencies on the machine you wish to run 4play on. In a sane setup, you usually want 4play to run on a dedicated user-grade machine. For the sake of simplicity, this machine will be referred as the "render server".

```sh
git clone https://git.lolcat.ca/lolcat/4get
cd 4get/extra/4play

# install nodejs 26.x or later if it's not already installed

npm install @lawlers/4play
sudo npm install -g nodemon

# edit line 7 where it says 'var password = "cnc";'. Use a strong password.
nano page-render.js

# test the server
nodemon page-render.js
```

I recommend setting up a systemd service to auto-start the 4play server:
```sh
sudo nano /etc/systemd/system/fplay.service
```

Paste this in (make sure to update `User` & `WorkingDirectory`)
```sh
[Unit]
Description=4play
After=network.target

[Service]
Type=simple
User=will
WorkingDirectory=/home/will/Desktop/4get/extra/4play/
ExecStart=nodemon /home/will/Desktop/4get/extra/4play/page-render.js
Restart=always
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
```

Now, enable the service
```sh
sudo systemctl enable fplay
sudo service fplay start
sudo service fplay status # check the status. You should see it running
```

This should expose a webservice located at `http://localhost:3000`. I highly recommend you put this endpoint behind apache2 or nginx to add SSL, if the render server sits on a different network.

## Setting up Firefox
- Install Firefox. You can go for the ESR version or the latest version. You'll figure it out.
- Head over to [this page](https://addons.mozilla.org/en-US/firefox/addon/4play/) or search "4play" on the Firefox extension store and install the extension.
- Click the settings cog next to the extension name. Enable "Run in Private Windows" and "Allow automatic updates"
- Click the extension in the top bar. Modify the `ws://localhost:3030/cnc` URL: replace the `cnc` path with the password you set earlier for the server.
- Use a timeout value of `30000` to avoid getting throttled by Firefox during reconnections.

If done correctly, you should see the red dot turn green in the extension's icon. You're ready to load pages!

## Setting up 4get
Now, you need to tell 4get to use the 4play service. Edit `data/config.php` and change these:

```php
const FPLAY_PASSWORD = "cnc"; # Change this to your password
const FPLAY_EXTERNAL_ENDPOINT = null; # Change null to "https://your-endpoint.com/api/example";, with the quotes and semicolon
```

**IMPORTANT:** By default, `localhost:3030` is what Firefox connects to. The render server is exposed at `localhost:3000`. Don't mix them up, otherwise you will get errors about an "invalid Upgrade header".

If everything was done correctly, you should be seeing results on the Google scraper! You can set proxies in `data/config.php` for it, just like any other scraper.
