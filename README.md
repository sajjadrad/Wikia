Wikia
=====

MediaWiki Markup Parser

Wikia is a MediaWiki markup parser based on "Mediawiki2HTML machine",but a lot of new markup and map generator added and
some patterns fixed.

How to Use
===
Wikia convert MediaWiki markup to html and is easy to use:
Just include wikia.inc.php and call parse function:

<code>$content=htmlspecialchars($content);<br/>
$map=generateMap($content);<br/>
echo parse($content);<br/></code>

In addition you can generate map of content headers by calling generateMap function.

IMPORTANT : CALL generateMap FUNCTION BEFORE PARSING.
