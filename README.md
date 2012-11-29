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

### And more!
Here are some of the features that are either implemented or planned.

* Designed to support the full gamut of project documentation, from the API reference to user guides, examples,
  screencasts, and other sorts of documentation.
* Leverages Reflection to parse the source code very, very quickly.
* Uses ApiGen's TokenReflection library to resolve namespace aliases back to fully-qualified class names.
* Uses an enhanced version of phpDocumentor 2's ReflectionDocBlock library for parsing DocBlocks.
* Uses Twig as a template engine.
* Support for multiple/custom templates.
* Support for multiple output formats including HTML5, RTF, Microsoft Word, iBooks (ePub), Kindle, PDF, Markdown,
  reStructuredText, LaTeX (and other TeX flavors), man pages, Mac OS X Dictionary definitions, etc. (planned)
* Support for Sphinx-compatible output. (planned; #142)
* Supports the latest version of the PHPDoc spec.
* Supports draft specs for @method, @property, @param and @alias.
* Detailed documentation of namespaces, classes, traits, interfaces, methods, constants and properties.
* Driven by a config file or by the command line.
* Leverages Symfony Event Dispatcher, enabling developers to modify behavior at runtime.
* Highlighted source code samples and examples.
* Optimizes for workflows that include Git and Composer.
* Indexes for classes, interfaces, traits, exceptions, deprecated elements, and to-do tasks.
* Enables you to exclude classes, methods or visibility based on patterns.
* Enables you to lookup per-file author information from GitHub.
* Support for grouping methods by task.
* Rich with Google-friendly metadata (including Google Verification & Analytics).
* Supports Disqus, Intense Debate and Facebook commenting systems out of the box.
* Supports Windows 7 Pinned Sites and Windows 8 Metro tiles.
* Supports Facebook, Twitter and Google+ social buttons (if enabled).
* Create a downloadable archive from your documentation. `zip`, `tgz`, `tbz2`, `7z` and `xz` are supported out of
  the box. (planned; #114, #115, #116, #117, #118, #119)
* Provides warnings/reports for poorly-documented elements. Checkstyle support is planned. (#138)
* Documentation of internal PHP classes that are extended. (planned; #120, #121, #122, #123, #124, #125, #126, #127,
  #128, #129, #130)
* Provides links to the starting line of an element in its source code. (planned; #139)
* List of direct and indirect known subclasses, implementers and users for every class/interface/trait/exception.
  (planned; #92)
* Ability to self-update by checking for a new version. (planned; #140)
* Google Custom Search support. (planned; #141)
* OpenSearch support. (planned; #103)
* Sitemap support.
* Support for documenting multiple versions of your software. (planned)
* User guide content can be written in HTML, Markdown, reStructuredText, or one of several other formats.
* Easily manage user guide chapters, figures, and other content.
* Support for internationalization in templates. (planned; #145)
* Has a corporate-friendly [MIT license](http://www.opensource.org/licenses/mit-license.php).

### Not quite yet!
**Vanity 3 is not yet ready for prime-time!** It is still being actively re-written
from the ground-up. The majority of the core parsing features are working. The default
HTML template is nearly complete, but is not yet usable. Check out the
[milestones](https://github.com/vanity/vanity/issues/milestones) to better understand where development is.


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

Assuming you already have [Composer](http://getcomposer.org) installed as `composer`:

    git clone git://github.com/vanity/vanity.git &&
    cd vanity &&
    composer install


## How to use

Learn about the available commands:

    vanity

Get help with a specific command:

    vanity help <command>


## Development/Contributing

Report issues to <https://github.com/vanity/vanity/issues/>.


## On the shoulders of giants
Vanity uses a number of off-the-shelf components to handle a variety of complex
tasks. These include several [Symfony 2 components](https://github.com/symfony/),
the [DocBlock parser](https://github.com/phpDocumentor/ReflectionDocBlock) from
phpDocumentor 2, the [PHP Tokenizer](https://github.com/Andrewsville/PHP-Token-Reflection)
from ApiGen, a modern [Markdown module](https://github.com/dflydev/dflydev-markdown),
[Monolog](https://github.com/Seldaek/monolog) for logging,
[Pandoc](http://johnmacfarlane.net/pandoc/) for format transliteration, and
[Twitter Bootstrap](http://twitter.github.com/bootstrap/)
and [jQuery](http://jquery.com) for much of the front-end code.

The feature set for Vanity 3 was inspired by projects such as
[phpDocumentor](http://phpdoc.org), [ApiGen](http://apigen.org),
[Sami](https://github.com/fabpot/Sami), [Rails API](http://railsapi.com),
[Ingredients.app](http://fileability.net/ingredients/),
[Apple](https://developer.apple.com/library/mac/navigation/),
[Parse](https://parse.com/docs/rest), Stripe's [REST](https://stripe.com/docs/api)
and [JavaScript](https://stripe.com/docs/stripe.js) APIs, and
[PHP.net](http://php.net/json_encode).

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
