---
layout: post
title: "Sass: One Year On"
summary: >
  Zarino debunks some myths about CSS pre-processors, and explains why he started using Sass at mySociety
related:
  - /post/git-push-to-deploy
  - /post/git-set-upstream
---

Until about this time last year, I’d never used a CSS pre-processor. In fact, I’d recoiled with horror when then-ScraperWiki-intern [Matthew Hughes](https://twitter.com/matthewhughes) asked me for help styling [a side-project](http://www.matthewhughes.co.uk/really-scrapable-web-app-is-a-new-way-to-learn-about-web-scraping/) that was built with [Less](http://lesscss.org), a common CSS pre-processor.

CSS preprocessors, for those not in the know, allow you to write styling rules for webpages in some sort of pseudo-CSS language, which you then compile into *real* CSS, for web browsers to understand, using a command line tool.

It’s not that I was ignorant. I’d flirted with Less, [Sass](http://sass-lang.com), and [Stylus](https://learnboost.github.io/stylus) for a year or two already. But I was always put off by three major hurdles:

![An example of Sass code](/media/sass-indenting.png)

## Misunderstanding #1: I have to learn a stupid new language

Whenever I’d actually attempted to write Sass, Less, or Stylus, it had been as a short-term volunteer for a friend in need. The last thing you want when you’re parachuting in to diagnose a mysterious CSS bug is a **requirement to first learn some completely arcane new language**. What does this `$` symbol mean? How’s it different to `&` and `@`? Is whitespace important? Are round brackets required? What’s this `@import` function, and how’s it different to the built-in CSS `@import()`?[^1] – What a mess!!

[^1]: The Sass version of `@import` takes styles and sucks them into a single output spreadsheet – the vanilla CSS `@import()` tells the web browser to load styles from a completely separate file. Sass variables and mixins, obviously, only work if stylesheets are combined using the Sass `@import` function.

This was further exacerbated by the fact that the styles and line numbers I saw in the web browser’s inspector **didn’t match up with the code and line numbers in the source files**. Even once a problem had been diagnosed in the browser, it was a game of hide and seek to track down the line of code that *actually generates* that problematic style. Functions and variables that obfuscate text strings under a layer of abstraction made the job *even harder* when you weren’t already familiar with the code.

Also, it sounds petty, but I was irreversibly put off by Sass and Stylus’s **Ruby-hipster-compatible lack of punctuation**. Why create a brand new domain-specific language for styling webpages when *we already have one* – It’s called CSS! Just to remove the semi-colons?? Oh get a life.

## Misunderstanding #2: Nesting makes styles harder to read

Not wanting to learn a new language is understandable. Even after I’d discovered [SCSS](http://sass-lang.com) (see below), the learning curve of picking it up and using it in anger at mySociety took months.

Being worried about nesting, however, was, in hindsight, ridiculous. Nesting is one of the best features in all three CSS pre-processors.

Nesting allows you to save time and space by constructing complex selectors based on selectors you’ve already written further up the document. In a typical project it probably only saves a few hundred bytes (even less if you’re a [BEM](https://en.bem.info/method/definitions/) acolyte). But the clarity it adds to the structure of your files is worth it alone.

Perhaps most amazingly, media queries can be nested inside or outside of other selectors, which means, when you have a specific fix for a specific element at a particular resolution, you can just put the media query *inside* the target selector, rather than duplicating the selector in a media query block somewhere else further down the file.

A year ago, however, I didn’t know any of this. Nesting was just something that promoted the creation of **over-specific selectors** and that made searching for full selectors in your text editor even harder.

## Misunderstanding #3: Compiling is slow and tedious and ruins my workflow

For a front-end developer used to files, uploaded by FTP and served statically by Apache, the brave new world of getting Sass or Less source code compiled into CSS was a complete unknown – and a **significant time investment to set up**.[^2]

[^2]: To install Less you need NPM. And to get NPM, you need Node.js and Git, neither of which comes by default on a Mac. Sass requires Ruby which, thankfully, is already on a Mac, in some truly ancient version. It’s a mess of dependencies, and all completely separate from the Apple-sanctioned walled garden of things you get from the App Store or icons you drag into your Applications folder.

Most web projects get to that point where you’re in a tight little cycle of tweaking a number in the CSS, checking the result in a web browser, and either tweaking it again or moving onto another quick change. Compiling, at the time, just seemed like a huge barrier in the middle of this cycle, where changes could only be checked after the *whole* CSS file had been regenerated – even if only a single character had changed.

(Little did I know, within only a few months, improvements to the Less and Sass compilers would make them much faster, and additional features like the `--watch` command line argument would reduce the need to keep switching to the terminal to trigger recompilation.)

* * *

So that’s why I never tried CSS pre-compilers in my personal projects or the work we did at ScraperWiki. In fact, I remember being asked what I thought of CSS pre-compilers, by [mySociety’s existing designer, Martin](http://mynameismartin.com), when I interviewed there last January. I said I thought they were interesting, but made it harder to contribute to new projects, and for very little gain over standard, hand-written CSS. I remember Martin smiling with a mixture of surprise and painful recognition.

Truth is, I’d misjudged CSS pre-processors. Partly because they were still a maturing technology while I was investigating them in 2011–2012, and partly because there was very little authoritative documentation on how a typical MAMP-based frontend developer like me should start using them.

But since then, things have changed. Documentation might still be thin on the ground, but the tangle of competing languages (remember [Stylus](https://learnboost.github.io/stylus/), anyone?) has at least finally settled on a winner (Sass/SCSS), and even CSS framework behemoth [Bootstrap](http://getbootstrap.com) has announced its [next release will be written in SCSS](https://twitter.com/mdo/status/591364406816079873).

So, in case you’re new to the party, here’s three reasons Sass is awesome:

## Killer feature #1: Forget Sass and Less, it’s all about SCSS

The Eureka moment for me was when I realised Sass (the ridiculous Ruby-inflected whitespace dialect) had an alternate form, called SCSS, which **looks identical to standard CSS**. Even the special language constructs like function definitions, if-elses, and for-loops start with an `@` symbol, continuing the tradition of `@`-prefixed CSS constructs like `@import`, `@media`, and `@font-face`.

And, appealing to my youth as a PHP developer, variables in SCSS start with a `$` symbol, rather than the `@` used in Less, which helps reduce confusion between variables and functions.

![An example of SCSS code](/media/scss-nesting.png)

Perhaps the best thing about SCSS’s similarity to CSS is **backward compatibility**. Upgrading a project to use a CSS pre-processor? Just change your file extension from `.css` to `.SCSS` and you’ve got a valid SCSS file. Then you can dip into special SCSS features at your own pace. No rush.

The SCSS syntax is an absolute gift for increasing adoption. No wonder the official Sass documentation now shows code snippets in the SCSS-flavoured syntax by default. I wouldn’t be surprised if, in a few years, the original syntax disappears entirely.

## Killer feature #2: Variables

![SCSS variables](/media/scss-variables.png)

Variables were a rarely discussed feature when I first enountered pre-processors. Maybe people thought they were too boring to mention. But they’re arguably the killer feature. **No more search-and-replacing** colour definitions across a folder of CSS files. And no more guessing what colour some random hex code is meant to represent.

With variables, you can define your site’s colours, font families, and media query breakpoints in advance, at the start of your file, or in their own imported file, and then use them whenever you need them. And because they’re named, they’re much easier to use. What’s quicker to recognise: `@media (min-width: 30em)` or `@media (min-width: $tablet-width)`.

Variables also give you the opportunity to **customise imported libraries** like [Foundation](http://foundation.zurb.com) and [Bootstrap](https://github.com/twbs/bootstrap-sass). I’d previously spent days overriding Bootstrap selectors at ScraperWiki. Now, removing border-radius from every element in a library, or changing the default colours, was as simple as declaring one or two variables and hitting “compile.”

## Killer feature #3: Loops and conditions

![A for loop in SCSS](/media/scss-loops.png)

A while back, I had to generate CSS for a background image sprite. There were 150 individual images, split across three sprite files (to get round iOS Safari’s 3 megapixel limit for background images). I *could* have written the 150 style definitions by hand. It probably would have taken a few hours.

But, instead, I used a `@for` loop in SCSS and, in five or six lines, I’d outsourced all the hassle to the compiler. I just had to tell it how many sprites there were, and what the dimensions of the image were, and I was done. SCSS generated the 150-odd selectors automatically, in the blink of an eye. And even better, if something changed later on, I only had to tweak it in one place, rather than find-and-replacing across 150 separate styles.

![A more complicated for loop in SCSS](/media/scss-sprites.png)

As you can see above, conditions, too, are really useful. Sometimes, in a for-loop, you’ll want to execute some special behaviour after a specific number of iterations (eg: clearing a float after every *n* items in a grid). Or other times, you might be making a module that is shared across multiple projects (this happens a *lot* at mySociety!) and you need to execute only certain parts of the stylesheet based on a variable defined in the parent file. `@if` and `@else` are perfect for all of this, and completely impossible in standard CSS.

In case you’re interested, here’s a segment of the CSS output by that `@for` loop above:

![CSS for an image sprite](/media/scss-sprites-compiled.png)

---

## Not all plain sailing

Even now, [CSS pre-processors have their critics](https://blog.colepeters.com/on-writing-real-css-again/). Some designers argue that they abstract you from the actual medium of browser rendering. Or that, if you’re writing CSS with for-loops, your CSS is too complex.

And, to an extent, they’re both right.

But it’s interesting that nobody makes the same arguments about [Django](https://docs.djangoproject.com/en/1.8/topics/templates/#the-django-template-language), [ERB](http://www.stuartellis.eu/articles/erb/), or [Liquid](http://jekyllrb.com/docs/templates/), which are templating languages, basically **just like SCSS**.

The fact is, you can write *efficient* SCSS and you can write *inefficient* SCSS, just as you can write *efficient* or *inefficient* CSS. It’s not often you’ll need a `@for` loop, but when you do, chances are, you *really* need it. Even just [DRY](http://en.wikipedia.org/wiki/Don%27t_repeat_yourself)-ing your CSS code by adding variables is a 100% improvement for code quality over manually find-and-replacing hex codes in raw CSS files.

 **Anyway, enough talk.** If you haven’t already, it’s time to go check out SCSS: <http://sass-lang.com>.
