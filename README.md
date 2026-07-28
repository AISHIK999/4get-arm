# 4get arm
4get search engine for arm64 devices. Original project: https://git.lolcat.ca/lolcat/4get

## About
**4get** is a proxy search engine

## Official instance
https://4get.ca , or visit the official instance list: https://4get.ca/instances

_NOT to be confused with 4get.ch, 4get.lol and friends! I **don't** host these._

## Features
1. Rotating proxies on a per-scraper basis
2. Search filters
3. Bot protection (per configuration)
4. Interface doesn't require javascript
5. Favicon fetcher with caching support & image proxy
6. Bunch of other shits

# Supported websites

| web          | images       | videos       | news         | music      | autocomplete |
|--------------|--------------|--------------|--------------|------------|--------------|
| DuckDuckGo   | DuckDuckGo   | YouTube      | DuckDuckGo   | SoundCloud | Brave        |
| Brave        | Yandex       | Vimeo        | Brave        | Swisscows  | DuckDuckGo   |
| Yandex       | Brave        | Sepia Search | Google       |            | Yandex       |
| Google       | Google       | DuckDuckGo   | Yahoo! JAPAN |            | Google       |
| Google API   | Google API   | Brave        | Startpage    |            | Startpage    |
| Google CSE   | Google CSE   | Yandex       | Qwant        |            | Kagi         |
| Yahoo! JAPAN | Yahoo! JAPAN | Google       | Mojeek       |            | Qwant        |
| Startpage    | Startpage    | Yahoo! JAPAN | Baidu        |            | Ghostery     |
| Qwant        | Qwant        | Startpage    |              |            | Yep          |
| Ghostery     | Baidu        | Qwant        |              |            | Marginalia   |
| Yep          | Solofield    | Baidu        |              |            | YouTube      |
| Mwmbl        | Pinterest    | Coc Coc      |              |            | SoundCloud   |
| Mojeek       | Cara         | Solofield    |              |            |              |
| Baidu        | Flickr       |              |              |            |              |
| Coc Coc      | Pexels       |              |              |            |              |
| Solofield    | Pixabay      |              |              |            |              |
| Marginalia   | Unsplash     |              |              |            |              |
| wiby         | 500px        |              |              |            |              |
|              | VSCO         |              |              |            |              |
|              | Imgur        |              |              |            |              |
|              | FindThatMeme |              |              |            |              |

# Installation

Docker (recommended):

```
sudo docker compose up --build -d
```

### OR

Refer to the <a href="https://github.com/AISHIK999/4get-arm/tree/master/docs/">documentation index</a>. I recommend following the <a href="https://github.com/AISHIK999/4get-arm/tree/master/docs/apache2.md">apache2 guide</a>.

## License
AGPLv3-only
