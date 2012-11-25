# TODO

## Stage 1: API Reflection and Tokenization

* Keep track of aliases and add metadata to the aliased methods. (2)
* Add support for method grouping. (3)
* Lookup known subclasses.
* Implement warnings/reports for TODOs. (3)
* Implement support for linking to PHP functions/classes (`get_defined_functions()`). (2)

* Add support for handling sub-blocks for @method. (8) (delaying)
* Add support for handling sub-blocks for @property. (2) (delaying)
* Add support for handling sub-blocks for @param. (2) (delaying)


## Stage 2: PHP Documentation Tokenization (delaying)

* Identifying and looking up the correct file(s) for extended PHP classes. (2)
* Normalize the DocBook and XML Schema source into something sensible. (2)
* Add support for pulling data that ReflectionClass would otherwise retrieve. (3)
* Add support for pulling data that ReflectionProperty would otherwise retrieve. (2)
* Add support for pulling data that ReflectionConstant would otherwise retrieve. (1)
* Add support for pulling data that ReflectionMethod would otherwise retrieve. (2)
* Add support for pulling data that ReflectionFunctionAbstract would otherwise retrieve. (2)
* Add support for pulling data that ReflectionParameter would otherwise retrieve. (2)
* Implement warnings/reports for mismatched types. (2)
* Implement warnings/reports for parameter definitions. (3)


## Stage 3: API Output Templating

* Develop a documentation landing page with links out to various other types of documentation content.
* Develop a template for Desktop HTML output.
* Develop a template for reStructuredText output.
* [TBD]

<link rel="shortcut icon" href="http://cdn.last.fm/flatness/favicon.2.ico" />
<link rel="apple-touch-icon" href="http://cdn.last.fm/flatness/apple-touch-icon.png" />
<link rev="canonical" href="http://last.fm/+uChXT" />

<meta name="application-name" content="Ryan Parman"/>
<meta name="msapplication-TileColor" content="#58595B"/>
<meta name="msapplication-TileImage" content="8b35978c-2e91-4fe6-b4ca-5ded8cf3335e.png"/>
<meta name="msapplication-starturl" content="http://buildmypinnedsite.com" />
<meta name="msapplication-navbutton-color" content="#3480C0" />
<meta name="msapplication-tooltip" content="Start Build My Pinned Site" />
<meta name="msapplication-task" content="name=Develop for Internet Explorer 9; action-uri=http://www.beautyoftheweb.com/#/startdeveloping; icon-uri=/favicon.ico">
<meta name="msapplication-task" content="name=Attend a Web Camp; action-uri=http://www.beautyoftheweb.com/#/camps; icon-uri=/favicon.ico" />

<link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="GitHub">

<script>if(window.navigator&&window.navigator.loadPurpose==="preview"){window.location.href="https://www.icloud.com/topsites_preview/"};</script>

           var s = window.parent.location.search.match(/\?q=([^&]+)/);
           if (s) {
               s = decodeURIComponent(s[1]).replace(/\+/g, ' ');
               if (s.length > 0)
               {
                   $('#search').val(s);
                   panel.search(s, true);
               }
           }


http://developer.github.com/v3/#rate-limiting
Tags: https://api.github.com/repos/aws/aws-sdk-php/git/refs/tags/:version
Source: https://api.github.com/repos/aws/aws-sdk-php/git/trees/:sha
Source for src/: https://api.github.com/repos/aws/aws-sdk-php/git/trees/:sha?recursive=1
Committers: https://api.github.com/repos/aws/aws-sdk-php/commits?per_page=100&path=:path



Get list of authors for a file:
git blame -p README.md | grep committer-mail | sort -u | sed -n '1h;1!H;${;g;s/committer-mail <//g;s/>//g;p;}'

<?xml version="1.0"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc>http://docs.amazonwebservices.com/AWSSDKforPHP/latest/index.html</loc>
	</url>
</urlset>

## Stage 4: User Guide Parsing

* Implement a pattern for importing documentation content.
* Determine best way to order/group/tag individual chapters/guides.
* Leverage [Sphinx](http://sphinx.pocoo.org) (or possibly [Pandoc](http://johnmacfarlane.net/pandoc/)) to convert reStructuredText source files into structured JSON for improved portability.
* [TBD]


## Stage 5: User Guide Output Templating

* Design a template that can be used for both Sphinx as well as HTML output.
* Should maintain UI across both sets of docs as though they were a unified set of docs.
* [TBD]


## Stage 6: Bundling and Distribution

* Compress documentation into downloadable bundles (e.g., zip, tbz2).
* Produce a .phar package for app distribution.


## Done

* ~~Build a console-based application using the Symfony Console component. (5)~~
* ~~Reflect the source code and construct a structured JSON document. (8)~~
* ~~Add support for parsing DocBlocks for descriptions and @tags. (5)~~
* ~~Merge data from @param tags with ReflectionParameter data to get a complete picture of parameters. (3)~~
* ~~Add support for resolving namespace aliases back into fully-qualified namespaces (i.e., namespaces, aliases, class heirarchies, implementation heirarchies). (8)~~
* ~~Add support for logging. (2)~~
* ~~Add support for determining the list of PHP extensions that are requirements for the extended code. (2)~~
* ~~Enable a custom directory for configuration data. (1)~~
* ~~Synchronize the coding style (currently a mix of SimplePie-style and PSR-1). (3)~~
* ~~Establish a pattern of notifying various actions with events and event handlers. (3)~~
* ~~Add support for collecting and logging mismatched parameter data so that we can find and fix it. (3)~~
* ~~Synchronize the format of event names and fix existing ones (e.g., parser.method.tag.param, config.api.warn.todo). (1)~~
* ~~Add a custom Event type which allows contextual storage. (1)~~
* ~~Make type-matching of native types case-insensitive. (1)~~
* ~~Add support for resolving the `self` keyword. (1)~~
* ~~Add fully-qualified class name resolution to the @see handler. (1)~~
* ~~Extract method handling into a separate class. (3)~~
* ~~Add event hooks for all @tags. (2)~~
* ~~Add complete support for license identifiers <http://www.spdx.org/licenses/>. (2)~~
* ~~Add support for @event tags. (3)~~
* ~~Add service enhancement support for @link. (2)~~
* ~~Add service enhancement support for @author. (2)~~
* ~~Add support for @property tags. (1)~~
* ~~Merge data from @property tags into the main "properties" hash. (2)~~
* ~~Add support for @method tags. (1)~~
* ~~Fix broken regexes for methods. (5)~~
* ~~Add support for reliably parsing inline tags. (5)~~
* ~~Add support for inline {@see} tags. (2)~~
* ~~Add support for inline {@internal} tags. (1)~~
* ~~Add support for inline {@example} tags. (1)~~
* ~~Add support for inline {@example} tags. (1)~~
* ~~Merge data from @method tags into the main "methods" hash. (3)~~
* ~~Fix issue with logging to the Vanity directory. (1)~~
* ~~Add support for inline {@inheritdoc} tags. (2)~~
* ~~Add support for documenting Interfaces (`get_declared_interfaces()`). (3)~~
* ~~Add support for documenting Traits (`get_declared_traits()`). (3)~~
* ~~Make event log severities user-configurable. (1)~~
* ~~Clean-up leftover @return and @param metadata tags.~~
* ~~Initial hooking-up of Twig and configuring the right options for generation.~~


