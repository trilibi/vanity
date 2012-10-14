# TODO

## Already Done

* ~~Build a console-based application using the Symfony Console component. (5)~~
* ~~Reflect the source code and construct a structured JSON document. (8)~~
* ~~Add support for parsing DocBlocks for descriptions and @tags. (5)~~
* ~~Merge data from @param tags with ReflectionParameter data to get a complete picture of parameters. (3)~~
* ~~Add support for resolving namespace aliases back into fully-qualified namespaces (i.e., namespaces, aliases, class heirarchies, implementation heirarchies). (8)~~
* ~~Add support for logging. (2)~~
* ~~Establish a pattern of notifying various actions with events and event handlers. (3)~~
* ~~Add support for collecting and logging mismatched parameter data so that we can find and fix it. (3)~~
* ~~Add support for determining the list of PHP extensions that are requirements for the extended code. (2)~~
* ~~Enable a custom directory for configuration data. (1)~~
* ~~Make type-matching of native types case-insensitive. (1)~~
* ~~Add support for resolving the `self` keyword. (1)~~
* ~~Extract method handling into a separate class. (3)~~
* ~~Synchronize the coding style (currently a mix of SimplePie-style and PSR-1). (3)~~
* ~~Add event hooks for all @tags. (2)~~
* ~~Add complete support for license identifiers <http://www.spdx.org/licenses/>. (2)~~

## Stage 1: API Reflection and Tokenization

* ~~Synchronize the format of event names and fix existing ones (e.g., parser.method.tag.param, config.api.warn.todo). (1)~~
* ~~Add a custom Event type which allows contextual storage. (1)~~
* Add support for @property tags. (1)
	* Merge data from @property tags into the main "properties" hash. (2)
* Add support for @method tags. (1)
	* Merge data from @method tags into the main "methods" hash. (3)
* Add support for @example tags. (3)
* Add support for inline {@see} tags. (1)
* Add support for inline {@inheritdoc} tags. (2)
* Add support for inline {@example} tags. (1)
* Add support for method grouping. (3)
* Implement warnings/reports for TODOs. (3)
* Implement cross-linking support. (3)
* Implement support for linking to PHP functions/classes. (2)


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

* Develop a documentation landing page with links out to various other types of documentation content. (2)
* Develop a template for Desktop HTML output. (8)
* Develop a template for reStructuredText output. (5)
* [TBD]


## Stage 4: User Guide Parsing

* Implement a pattern for importing documentation content. (2)
* Determine best way to order/group/tag individual chapters/guides. (3)
* Leverage [Sphinx](http://sphinx.pocoo.org) (or possibly [Pandoc](http://johnmacfarlane.net/pandoc/)) to convert reStructuredText source files into structured JSON for improved portability. (???)
* [TBD]


## Stage 5: User Guide Output Templating

* Design a template that can be used for both Sphinx as well as HTML output. (???)
* Should maintain UI across both sets of docs as though they were a unified set of docs.
* [TBD]


## Stage 6: Bundling and Distribution

* Compress documentation into downloadable bundles (e.g., zip, tbz2). (2)
* Produce a .phar package for app distribution. (2)


# Pandoc Notes
## RST to DocBook

## RST to HTML(5)

## RST to LaTeX
pandoc index.rst --output index.latex --read rst --write latex --template ../../vendor/vanity/pandoc-templates/default.latex --latex-engine=lualatex --variable mainfont="SourceSansPro-Regular" --variable sansfont="SourceSansPro-Regular" --variable monofont="MesloLGS" --toc --number-sections --chapters --no-tex-ligatures

## RST to Markdown
pandoc index.rst --output index.md --read rst --write markdown --template ../../vendor/vanity/pandoc-templates/default.markdown --toc --smart --standalone --number-sections --chapters --atx-headers --strict

## RST to MediaWiki
pandoc index.rst --output index.wiki --read rst --write mediawiki --template ../../vendor/vanity/pandoc-templates/default.mediawiki --toc --standalone

## RST to OpenDocument (ODT)
pandoc index.rst --output index.odt --read rst --write odt  --reference-odt ../../src/Pandoc/templates/odt_styles.odt --toc --number-sections --chapters

## RST to PDF
pandoc index.rst --output index.pdf --read rst --template ../../vendor/vanity/pandoc-templates/default.latex --latex-engine=lualatex --variable mainfont="SourceSansPro-Regular" --variable sansfont="SourceSansPro-Regular" --variable monofont="MesloLGS" --toc --number-sections --chapters --no-tex-ligatures

## RST to RTF
pandoc index.rst --output index.rtf --read rst --write rtf --template ../../vendor/vanity/pandoc-templates/default.rtf --toc --smart --standalone --number-sections --chapters

## RST to Textile
pandoc index.rst --output index.textile --read rst --write textile --template ../../vendor/vanity/pandoc-templates/default.textile --toc --standalone --number-sections --chapters

## RST to Word
pandoc index.rst --output index.docx --read rst --write docx  --reference-docx ../../src/Pandoc/templates/docx_styles.docx --toc --number-sections --chapters
