# Vanity 3

**Vanity enables you to create a world-class documentation experience for your
PHP projects.**

## What makes Vanity different?
By supporting a more _complete_ documentation experience than an API Reference
alone, Vanity makes it easy to produce user guides, cookbooks & tutorials,
screencasts and more!

### Speed
At its core, Vanity is designed for speed. By focusing exclusively on PHP, it is
able to leverage PHP's super-fast reflection engine to document your source code.
When Vanity encounters something that isn't supported by the reflection engine, it
switches into tokenization mode to provide additional data.

### Helpfulness
Using this thorough understanding of your source code, Vanity can optionally
provide feedback about system requirements, inconsistent documentation, and other
things that can pro-actively help you provide a better documentation experience
for your users. Vanity also encourages best practices by supporting
[Composer](http://getcomposer.org) and the
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
standard out-of-the-box.

### User-experience
Vanity's templates were designed with user-experience and style in mind. You get
attractive templates right out of the box that are designed for humans first.
They're also designed to rank highly in organic search engine results, making it
easy for your customers to find the latest information.

### Customizability
Do you like to tweak things? Would you like to support a new output type? Vanity's
strict separation of its data from its templates makes it easy to modify an existing
template, or even write an entirely new one yourself. Vanity is fundamentally
designed to be flexible and customizable so that it can easily work with your
projects.

### Not quite yet!
**Vanity 3 is not yet ready for prime-time!** It is still being actively re-written
from the ground-up. Most of the core is working, but none of the templating engine
work has been started yet. Check the [TODO.md] file for more information.


## Requirements
### Required
The following software is **required** for Vanity to run:

* [PHP](http://php.net) 5.3.2+

### Optional
This software is **optional**, and is only used if you need to generate additional output formats.

* [pandoc](http://johnmacfarlane.net/pandoc/) (for generating **anything** besides Desktop/Mobile HTML)
* [texlive-latex]() (for generating LaTeX, PDF or ePub output)
* [KindleGen](http://www.amazon.com/gp/feature.html?ie=UTF8&docId=1000234621) (for generating Kindle-compatible eBooks)
* [Source Sans](http://sourceforge.net/projects/sourcesans.adobe/) (sans-serif font used for _text_ in PDF and ePub)
* [Meslo LG](https://github.com/andreberg/Meslo-Font/) (monospaced font used for _code_ in PDF and ePub)


## Installation
{TBD}


## How to use
{TBD}


## Development/Contributing
{TBD}


## On the shoulders of giants
Vanity uses a number of off-the-shelf components to handle a variety of complex
tasks. These include several Symfony 2 components, the DocBlock parser from
phpDocumentor 2, the PHP Tokenizer from ApiGen, a modern Markdown module,
Monolog for logging, Pandoc for format transliteration, and Twitter Bootstrap
and jQuery for much of the front-end code.

The feature set for Vanity 3 was inspired by projects such as phpDocumentor,
ApiGen, Sami, Rails API, Ingredients.app, Apple's developer docs, Parse, Stripe
and PHP.net.

A big thanks goes out to [Jeremy Lindblom](http://webdevilaz.com) and
[Michael Dowling](http://mtdowling.com) for frequent feedback,
[Ryan McCue](http://ryanmccue.info) for contributing to earlier versions of Vanity
(especially around Windows support), Mr. Zachary Bartholomew Layne for teaching me
how to be a better documentation writer, and to all of the CloudFusion and
[AWS SDK for PHP](http://aws.amazon.com/sdkforphp) customers who provided feedback
at one point or another about something that we could have been doing better.
This project wouldn't exist without you!


## Authors, Copyright & Licensing
* Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com).

See also the list of [contributors](./contributors) who participated in this project.

Licensed for use under the terms of the [MIT license](http://www.opensource.org/licenses/mit-license.php).
