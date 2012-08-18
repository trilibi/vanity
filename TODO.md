# TODO

## Stage 1: API Reflection and Tokenization

* Synchronize the format of event names and fix existing ones (e.g., parser.method.tag.param, config.api.warn.todo). (2)
* Synchronize the coding style (currently a mix of SimplePie-style and PSR-1). (3)
* Extract method handling into a separate class. (3)
* Extract parameter handling into a separate class. (2)
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


## Stage 2: PHP Documentation Tokenization

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


## Stage 4: User Guide Parsing

* Leverage [Sphinx]() to convert RST source files into structured JSON for portability.


## Stage 5: User Guide Output Templating


## Stage 6: Bundling and Distribution

* Produce a .phar package for app distribution.
