---
layout: post
title: "Self-hosting Google fonts"
summary: >
  Creating and self hosting your own WOFF and WOFF2 fonts isn’t hard. Here’s how to do it.
related:
  - /post/micropache-apache-mac/
  - /post/git-push-to-deploy/
---

[Google Fonts](https://fonts.google.com) is an incredibly popular way to use custom fonts on your web pages. You can choose from hundreds of open source fonts in Google’s library, and it’s all fast and free.

The thing is, handy as Google Fonts might be, it’s still a third-party dependency. Every new visitor to your site means another trip to Google’s servers, fetching that fancy font you want them to see. Google says their Fonts API is “designed to limit the collection, storage, and use of end-user data to what is needed to serve fonts efficiently”,[^1] but nonetheless, they’re tracking your visitors, so you at the very least need to include Google Fonts in your site’s Privacy Policy.

[^1]: <https://developers.google.com/fonts/faq#what_does_using_the_google_fonts_api_mean_for_the_privacy_of_my_users>

Sometimes it feels good to just cut out the third-party tracking, and serve up the fonts yourself. And thanks to widespread support for WOFF and WOFF2 font formats, it’s fairly easy to do.

Here’s how I did it in a recent project.

# But first, a word about licensing

It’s simple: Only convert a TTF to a webfont if you have permission to do so.

Fonts released under an open source license (such as the [SIL OFL](http://scripts.sil.org/OFL), or [Apache 2.0](http://www.apache.org/licenses/)) are all free to use, modify, and republish. So they’re good to go!

Most commercial font licenses, on the other hand, explicitly forbid publication as webfonts. It’s a pain, but hey, those designers gotta earn a living.

Here are a few places to find really awesome open source fonts:

* [Google Fonts](https://fonts.google.com)
* [Open Foundry](https://open-foundry.com/fonts)
* [Open Font Library](https://fontlibrary.org)
* [The League of Moveable Type](https://www.theleagueofmoveabletype.com)
* [Font Space – ‘open’ category](https://www.fontspace.com/category/open)

# Getting TTF files

You’ll want to start with TTF (TrueType) files for a font. [Google Fonts](https://fonts.google.com/) has recently added a “Download family” button to the font family pages on its site, [like this one, for Open Sans](https://fonts.google.com/specimen/Open+Sans). Clicking the button will download a zip archive containing a TTF file for each weight and style of the font.

For each TTF file you download, we’ll create a corresponding WOFF and WOFF2 file. This will give you fairly good browser support.

Then, finally, we’ll reference those WOFF and WOFF2 font files in your site’s CSS.

Let’s begin!

# Building WOFF files

WOFF is a compressed font format, supported by most web browsers released after 2011, including IE9–IE11.[^2]

[^2]: <https://caniuse.com/#feat=woff>

You can convert a TTF file to WOFF file using the [ttf2woff](https://github.com/fontello/ttf2woff) nodejs script. Here I am downloading the script, and running it on a `OpenSans-Regular.ttf` file I already downloaded:

```sh
git clone --recursive https://github.com/fontello/ttf2woff.git
cd ttf2woff
npm install
./ttf2woff.js OpenSans-Regular.{ttf,woff}
```

`ttf2woff.js` requires two arguments – a source `.ttf` file to convert, and a destination `.woff` file to create.

To save writing out the font’s filename twice, I’ve used the Bash shortcut `{ttf,woff}`. When the command runs, Bash spots the curly brackets, and expands the two arguments out (ie: `myfont.{ttf,woff}` becomes `myfont.ttf myfont.woff`).

# Building WOFF2 files

WOFF2 is a more efficient compression format, supported by almost all modern browsers, including Edge (14+), Firefox (39+), Chrome (36+), and Safari (12+).[^3]

[^3]: <https://caniuse.com/#feat=woff2>

Google maintains a command-line script to create WOFF2 files, in its [woff2 library](https://github.com/google/woff2):

```sh
git clone --recursive https://github.com/google/woff2.git
cd woff2
make clean all
./woff2_compress OpenSans-Regular.ttf
```

`woff2_compress` automatically creates a `.woff2` file with the same name as the input `.ttf` file.

# Writing your CSS

Now you can pop the `.woff` and `.woff2` files somewhere in your web directory, and reference them using `@font-face` rulesets in your CSS.

Here’s an example of me serving Open Sans in two styles (regular and italic) and two weights (regular and bold):

```css
@font-face {
    font-family: 'Open Sans';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: local('Open Sans Regular'), local('OpenSans-Regular'), url('/fonts/open-sans-regular.woff') format('woff'), url('/fonts/open-sans-regular.woff2') format('woff2');
}

@font-face {
    font-family: 'Open Sans';
    font-style: italic;
    font-weight: 400;
    font-display: swap;
    src: local('Open Sans Regular'), local('OpenSans-Regular'), url('/fonts/open-sans-italic.woff') format('woff'), url('/fonts/open-sans-italic.woff2') format('woff2');
}

@font-face {
    font-family: 'Open Sans';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: local('Open Sans Regular'), local('OpenSans-Regular'), url('/fonts/open-sans-bold.woff') format('woff'), url('/fonts/open-sans-bold.woff2') format('woff2');
}

@font-face {
    font-family: 'Open Sans';
    font-style: italic;
    font-weight: 700;
    font-display: swap;
    src: local('Open Sans Regular'), local('OpenSans-Regular'), url('/fonts/open-sans-bold-italic.woff') format('woff'), url('/fonts/open-sans-bold-italic.woff2') format('woff2');
}
```

It’s good practice to include the `local()` functions so that, if the user already has the font installed on their device, that local copy is used, instead of your webfont versions, saving a few precious bytes. But if you don‘t know the font’s name and Postscript name, you _can_ leave this bit out.

# Subsetting fonts

One nice thing that Google Fonts makes easy is requesting just a subset of the glyphs contained in a font. So if you know you’ll only ever need latin characters, say, you can ask Google Fonts to serve you a version of a font without any cyrillic or Vietnamese glyphs, often dramatically reducing the filesize.

If you want to try this yourself, you’ll need to install a few additional tools:

```sh
npm install -g font-ranger
pipx install fonttools
pipx inject fonttools brotli zopfli
```

And then run `font-ranger` over your TTF files, to generate the individual subsets:

```sh
for i in Montserrat-*.ttf; do font-ranger -f "$i" -o ../webfont-subsets -u latin latin-ext cyrillic cyrillic-ext vietnamese -n "${i%.*}" -w true; done
```

Good luck!
