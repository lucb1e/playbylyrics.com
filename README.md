# PlayByLyrics.com source code

I wrote this a long time ago. Please don't judge me too much :)

Since I didn't use version control, you will see a million versions spread out
in directories and subdirectories. Sorry about that.

Another tiny problem: the site doesn't work anymore. I don't use the site
anymore so I don't really maintain it, but if anyone wants to fix it, just
create a pull request. **If you make a lot of improvements, I might even give
you the domain for free.**

## Contributing

The site is currently broken, so that is priority number one. Other than that,
any kind of contribution is welcome. I will judge any pull requests and if I
think it's a positive change, I will upload the new code to the live website
(at playbylyrics.com).

Please keep the code style (indentation, variable naming, etc.) consistent with
the existing source.

## The code

As mentioned, there are a bunch of folders with random stuff. Important files
and folders:

- `includes/functions.php` contains a bunch of stuff, including the templating
  engine and some core functions (getGoogle and getYoutube).
- `res/` contains resources, which is currently only images and stylesheets. If
  you want to add audio or javascript files, this is the folder where it would
  go.
- `pages/` contains all pages on the site. Each page is called from `index.php`
  and each page is responsible for calling the template they want. A good
  example of that templating thing is `pages/about.php`.
- `templates/` contains templates.
- `ajaj/` contains stuff to be called from JavaScript.
- `log/` contains only the error log.
- `config.php` is unused, as far as I know. Maybe it's included somewhere, but
  I'm not aware that any of its settings are actually used.
- `index.php` bootstraps the whole party. If you need to find anything and it's
  not immediately obvious from this list, look there and work your way down.

Though the core code, calling the Youtube API and stuff, is quite nasty, I
think the 'new' system with pages and templates is relatively readable. Working
your way down from index.php to what you are looking from shouldn't be too
hard. ('New', well, it was a big revision and a big improvement from the
previous code, and I think one of the last changes I made... written around
June 2013.)

From a high level, how does it all tie together?

1. User submits query X.
2. The site will google for X + 'lyrics', using some mobile version of Google.
3. From the Google results, it grabs the title of the top result. The title
   usually contains artist and song name, so...
4. The title is searched for on Youtube using their API.
5. The resulting video is displayed and automatically played.

Q&A:

- Why not use the Google Search API? Because it's expensive once you get a lot of traffic. At least it was when I wrote this in ~2012.

- What does the 'Show me another result' button do? Instead of picking the top result, it picks the second result. Sort of. See main.php, it's quite near the top.

## License

The MIT License (MIT)

PHP code and texts: Copyright (c) 2015 lucb1e
Images, HTML and CSS: Copyright (c) 2015 Ramon Meffert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

