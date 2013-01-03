Wikia
=====

MediaWiki Markup Parser

Wikia is a MediaWiki markup parser based on "Mediawiki2HTML machine",but a lot of new markup and map generator added and
some patterns fixed.

How to Use
===
Wikia convert MediaWiki markup to html and is easy to use:
Just include wikia.inc.php and call parse function.
In addition you can generate map of content headers by calling generateMap function.

```php
require_once("wikia.inc.php");
$content=htmlspecialchars($content);
echo generateMap($content);
echo parse($content);
```


