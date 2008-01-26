<?php
chdir("../");
include("inc.php");

ModuleInit('formatting');




if( KeyInRequest('long') ) {

	$TEMPLATE->AddCrumb('',"The Long Version");
	PrintHeader();
	TheLongVersion();

} elseif( KeyInRequest('short') ) {

	$TEMPLATE->AddCrumb('',"The Short Version");
	PrintHeader();
	TheShortVersion();

} else {
	PrintHeader();
	?>

	<style type="text/css">
		#thelinks {
			margin-top: 40px;
		}

		#thelinks table {
			width: 400px;
			margin-left: auto;
			margin-right: auto;
		}

		#thelinks td {
			text-align: center;
		}

		#theshort a, #thelong a {
			background-color: #e6d09e;
			width: 200px;
			display: block;
			padding-top: 40px;
			padding-bottom: 40px;
			text-decoration: none;
			color: black;
			font-size: 18px;
			margin-right: 30px;
			border: 1px #cf9d2b solid;
		}

		#theshort a:hover, #thelong a:hover {
			background-color: #f2e7ce;
			color: black;
		}

	</style>

	<div id="thelinks">

		<table><tr>

		<td><div id="theshort">
		<a href="<?= $_SERVER['PHP_SELF'] ?>?short">The Short Version</a>
		</div></td>

		<td><div id="thelong">
		<a href="<?= $_SERVER['PHP_SELF'] ?>?long">The Long Version</a>
		</div></td>

		</tr></table>

	</div>

	<?
	echo str_repeat('<br>',20);
}





function TheShortVersion() {
PrintStyles();
?>
<div id="fh">

<div class="toc_list" id="toc">
	<strong>Table of Contents</strong>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc1">Inline Formatting</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc3">Headings</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc10">Lists</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc11">Bullet Lists</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc12">Numbered Lists</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc16">Links</a></div>
</div>
<br />
<hr />
<h2 class="yawiki" id="toc1">Inline Formatting</h2>


<table class="yawiki">
	<tr class="yawiki">
		<td class="yawiki"><tt>//emphasis text//</tt></td>
		<td class="yawiki"><em>emphasis text</em></td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>**strong text**</tt></td>
		<td class="yawiki"><strong>strong text</strong></td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>//**emphasis and strong**//</tt></td>
		<td class="yawiki"><em><strong>emphasis and strong</strong></em></td>
	</tr>
</table>

<hr />
<h2 class="yawiki" id="toc3">Headings</h2>
<p class="yawiki">You can make various levels of heading by putting plus signs before the text (all on its own line):</p>


<pre class="yawiki"><code>++ Level 2 Heading
+++ Level 3 Heading
++++ Level 4 Heading</code></pre>

<h2 class="yawiki" id="toc4">Level 2 Heading</h2>
<h3 class="yawiki" id="toc4">Level 3 Heading</h3>
<h4 class="yawiki" id="toc5">Level 4 Heading</h4>
<hr />

<h2 class="yawiki" id="toc9">Horizontal Rules</h2>
<p class="yawiki">Use four dashes (<tt>----</tt>) to create a horizontal rule.</p>

<hr />
<h2 class="yawiki" id="toc10">Lists</h2>
<h3 class="yawiki" id="toc11">Bullet Lists</h3>
<p class="yawiki">You can create bullet lists by starting a paragraph with one or more asterisks.</p>


<pre class="yawiki"><code>* Bullet one
* Bullet two
 * Sub-bullet</code></pre>


<ul>
	<li>Bullet one</li>
	<li>Bullet two<ul>
		<li>Sub-bullet</li>
	</ul></li>
</ul>

<h3 class="yawiki" id="toc12">Numbered Lists</h3>
<p class="yawiki">Similarly, you can create numbered lists by starting a paragraph with one or more hashes.</p>

<pre class="yawiki"><code># Numero uno
# Number two
 # Sub-item</code></pre>

<ol>
	<li>Numero uno</li>
	<li>Number two<ol>
		<li>Sub-item</li>
	</ol></li>
</ol>

<hr />
<h2 class="yawiki" id="toc16">Links</h2>

<h3 class="yawiki" id="toc19">URLs</h3>

<p class="yawiki">Create a remote link simply by typing its URL: <a href="http://ciaweb.net" onclick="window.open(this.href, '_blank'); return false;">http://ciaweb.net</a>.</p>

<p class="yawiki">You can provide alternate text for the link:<br />

<pre class="yawiki"><code>[http://www.google.com Google]</code></pre>

<a href="http://www.google.com" onclick="window.open(this.href, '_blank'); return false;">Google</a></p>
<br /><br />


</div>

<?php
}





function TheLongVersion() {
PrintStyles();
?>
<div id="fh">

<div class="toc_list" id="toc">
	<strong>Table of Contents</strong>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc0">General Notes</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc1">Inline Formatting</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc2">Literal Text</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc3">Headings</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc4">Level 3 Heading</a></div>
	<div class="toc_item" style="margin-left: 2em;"><a href="#toc5">Level 4 Heading</a></div>
	<div class="toc_item" style="margin-left: 3em;"><a href="#toc6">Level 5 Heading</a></div>
	<div class="toc_item" style="margin-left: 4em;"><a href="#toc7">Level 6 Heading</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc8">Table of Contents</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc9">Horizontal Rules</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc10">Lists</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc11">Bullet Lists</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc12">Numbered Lists</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc13">Mixing Bullet and Number List Items</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc14">Definition Lists</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc15">Block Quotes</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc16">Links and Images</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc17">Wiki Links</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc18">Interwiki Links</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc19">URLs</a></div>
	<div class="toc_item" style="margin-left: 1em;"><a href="#toc20">Images</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc21">Code Blocks</a></div>
	<div class="toc_item" style="margin-left: 0em;"><a href="#toc22">Tables</a></div>
</div>
<br />
<hr />
<h2 class="yawiki" id="toc0">General Notes</h2>
<p class="yawiki">The markup described on this page is for the default <tt>Text_Wiki</tt> rules; it is a combination of the <a href="http://tavi.sourceforge.net" onclick="window.open(this.href, '_blank'); return false;">WikkTikkiTavi</a> and <a href="http://develnet.org/" onclick="window.open(this.href, '_blank'); return false;">coWiki</a> markup styles.</p>

<p class="yawiki">All text is entered as plain text, and will be converted to HTML entities as necessary.  This means that <tt>&lt;</tt>, <tt>&gt;</tt>, <tt>&amp;</tt>, and so on are converted for you (except in special situations where the characters are Wiki markup; Text_Wiki is generally smart enough to know when to convert and when not to).</p>

<p class="yawiki">Just hit &quot;return&quot; twice to make a paragraph break.  If you want to keep the same logical line but have to split it across two physical lines (such as when your editor only shows a certain number of characters per line), end the line with a backslash <tt>\</tt> and hit return once.  This will cause the two lines to be joined on display, and the backslash will not show.  (If you end a line with a backslash and a tab or space, it will <i>not</i> be joined with the next line, and the backslash will be printed.)</p>

<hr />
<h2 class="yawiki" id="toc1">Inline Formatting</h2>


<table class="yawiki">
	<tr class="yawiki">
		<td class="yawiki"><tt>//emphasis text//</tt></td>
		<td class="yawiki"><em>emphasis text</em></td>
	</tr>

	<tr class="yawiki">
		<td class="yawiki"><tt>**strong text**</tt></td>
		<td class="yawiki"><strong>strong text</strong></td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>//**emphasis and strong**//</tt></td>
		<td class="yawiki"><em><strong>emphasis and strong</strong></em></td>

	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>{{teletype text}}</tt></td>
		<td class="yawiki"><tt>teletype text</tt></td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>@@--- delete text +++ insert text @@</tt></td>

		<td class="yawiki"><del> delete text </del><ins> insert text </ins></td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>@@--- delete only @@</tt></td>
		<td class="yawiki"><del> delete only </del></td>

	</tr>
	<tr class="yawiki">
		<td class="yawiki"><tt>@@+++ insert only @@</tt></td>
		<td class="yawiki"><ins> insert only </ins></td>
	</tr>
</table>

<hr />
<h2 class="yawiki" id="toc2">Literal Text</h2>

<p class="yawiki">If you don't want Text_Wiki to parse some text, enclose it in two backticks (not single-quotes).</p>


<pre class="yawiki"><code>
This //text// gets **parsed**.

``This //text// does not get **parsed**.``
</code></pre>

<p class="yawiki">This <em>text</em> gets <strong>parsed</strong>.</p>

<p class="yawiki">This //text// does not get **parsed**.</p>

<hr />
<h2 class="yawiki" id="toc3">Headings</h2>
<p class="yawiki">You can make various levels of heading by putting plus signs before the text (all on its own line):</p>


<pre class="yawiki"><code>+++ Level 3 Heading
++++ Level 4 Heading
+++++ Level 5 Heading
++++++ Level 6 Heading</code></pre>

<h3 class="yawiki" id="toc4">Level 3 Heading</h3>
<h4 class="yawiki" id="toc5">Level 4 Heading</h4>
<h5 class="yawiki" id="toc6">Level 5 Heading</h5>

<h6 class="yawiki" id="toc7">Level 6 Heading</h6>
<hr />
<h2 class="yawiki" id="toc8">Table of Contents</h2>
<p class="yawiki">To create a list of every heading, with a link to that heading, put a table of contents tag on its own line.</p>


<pre class="yawiki"><code>[[toc]]</code></pre>

<hr />
<h2 class="yawiki" id="toc9">Horizontal Rules</h2>
<p class="yawiki">Use four dashes (<tt>----</tt>) to create a horizontal rule.</p>

<hr />
<h2 class="yawiki" id="toc10">Lists</h2>
<h3 class="yawiki" id="toc11">Bullet Lists</h3>
<p class="yawiki">You can create bullet lists by starting a paragraph with one or more asterisks.</p>


<pre class="yawiki"><code>* Bullet one
 * Sub-bullet</code></pre>


<ul>
	<li>Bullet one<ul>

		<li>Sub-bullet</li>
	</ul></li>
</ul>

<h3 class="yawiki" id="toc12">Numbered Lists</h3>
<p class="yawiki">Similarly, you can create numbered lists by starting a paragraph with one or more hashes.</p>


<pre class="yawiki"><code># Numero uno
# Number two
 # Sub-item</code></pre>


<ol>
	<li>Numero uno</li>
	<li>Number two<ol>
		<li>Sub-item</li>
	</ol></li>
</ol>

<h3 class="yawiki" id="toc13">Mixing Bullet and Number List Items</h3>
<p class="yawiki">You can mix and match bullet and number lists:</p>


<pre class="yawiki"><code># Number one
 * Bullet
 * Bullet
# Number two
 * Bullet
 * Bullet
  * Sub-bullet
   # Sub-sub-number
   # Sub-sub-number
# Number three
 * Bullet
 * Bullet</code></pre>


<ol>
	<li>Number one<ul>
		<li>Bullet</li>
		<li>Bullet</li>
	</ul></li>

	<li>Number two<ul>
		<li>Bullet</li>
		<li>Bullet<ul>
			<li>Sub-bullet<ol>
				<li>Sub-sub-number</li>
				<li>Sub-sub-number</li>

			</ol></li>
		</ul></li>
	</ul></li>
	<li>Number three<ul>
		<li>Bullet</li>
		<li>Bullet</li>
	</ul></li>

</ol>

<h3 class="yawiki" id="toc14">Definition Lists</h3>
<p class="yawiki">You can create a definition (description) list with the following syntax:</p>


<pre class="yawiki"><code>: Item 1 : Something
: Item 2 : Something else</code></pre>

<dl>
	<dt>Item 1</dt>
		<dd>Something</dd>

	<dt>Item 2</dt>
		<dd>Something else</dd>
</dl>

<hr />
<h2 class="yawiki" id="toc15">Block Quotes</h2>
<p class="yawiki">You can mark a blockquote by starting a line with one or more '&gt;' characters, followed by a space and the text to be quoted.</p>


<pre class="yawiki"><code>This is normal text here.


&gt; Indent me! The quick brown fox jumps over the lazy dog. \
Now this the time for all good men to come to the aid of \
their country. Notice how we can continue the block-quote \
in the same &quot;paragraph&quot; by using a backslash at the end of \
the line.
&gt;
&gt; Another block, leading to...
&gt;&gt; Second level of indenting.  This second is indented even \
more than the previous one.

Back to normal text.</code></pre>

<p class="yawiki">This is normal text here.</p>

<blockquote><p class="yawiki">Indent me! The quick brown fox jumps over the lazy dog. Now this the time for all good men to come to the aid of their country. Notice how we can continue the block-quote in the same &quot;paragraph&quot; by using a backslash at the end of the line.<br />

Another block, leading to...</p>

	<blockquote><p class="yawiki">Second level of indenting.  This second is indented even more than the previous one.</p>

	</blockquote>
</blockquote>
<p class="yawiki">Back to normal text.</p>

<hr />
<h2 class="yawiki" id="toc16">Links and Images</h2>

<h3 class="yawiki" id="toc19">URLs</h3>

<p class="yawiki">Create a remote link simply by typing its URL: <a href="http://ciaweb.net" onclick="window.open(this.href, '_blank'); return false;">http://ciaweb.net</a>.</p>

<p class="yawiki">If you like, enclose it in brackets to create a numbered reference and avoid cluttering the page; <tt>[http://ciaweb.net/free/]</tt> becomes <sup><a href="http://ciaweb.net/free/" onclick="window.open(this.href, '_blank'); return false;">1</a></sup>.</p>

<p class="yawiki">Or you can have a described-reference instead of a numbered reference:<br />

<pre class="yawiki"><code>[http://pear.php.net PEAR]</code></pre>

<a href="http://pear.php.net" onclick="window.open(this.href, '_blank'); return false;">PEAR</a></p>
<br /><br />

<h3 class="yawiki" id="toc20">Images</h3>
<p class="yawiki">You can put a picture in a page by typing the URL to the picture (it must end in gif, jpg, or png).<br />

<pre class="yawiki"><code>http://c2.com/sig/wiki.gif</code></pre>

</p>

<p class="yawiki"><img src="http://c2.com/sig/wiki.gif" alt="http://c2.com/sig/wiki.gif" /></p>

<p class="yawiki">You can use the described-reference URL markup to give the image an ALT tag:<br />

<pre class="yawiki"><code>[http://lanecc.edu/images/fall07covert.jpg Aspire]</code></pre>

</p>

<p class="yawiki"><img src="http://lanecc.edu/images/fall07covert.jpg" alt="Aspire" /></p>

<hr />
<h2 class="yawiki" id="toc21">Code Blocks</h2>
<p class="yawiki">Create code blocks by using <tt>&lt;code&gt;...&lt;/code&gt;</tt> tags (each on its own line).</p>


<pre class="yawiki"><code>This is an example code block!</code></pre>

<p class="yawiki">To create PHP blocks that get automatically colorized when you use PHP tags, simply surround the code with <tt>&lt;code type=&quot;php&quot;&gt;...&lt;/code&gt;</tt> tags (the tags themselves should each be on their own lines, and no need for the <tt>&lt;?php ... ?&gt;</tt> tags).</p>

<pre class="yawiki"><code> &lt;code type=&quot;php&quot;&gt;
 // Set up the wiki options
 $options = array();
 $options['view_url'] = &quot;index.php?page=&quot;;

 // load the text for the requested page
 $text = implode('', file($page . '.wiki.txt'));

 // create a Wiki objext with the loaded options
 $wiki = new Text_Wiki($options);

 // transform the wiki text.
 echo $wiki-&gt;transform($text);
 &lt;/code&gt;</code></pre>


<pre class="yawiki"><code><span style="color:#000000">
<span style="color:#0000BB">&lt;?php
</span><span style="color:#FF8000">// Set up the wiki options

</span><span style="color:#0000BB">$options </span><span style="color:#007700">= array();
</span><span style="color:#0000BB">$options</span><span style="color:#007700">[</span><span style="color:#DD0000">'view_url'</span><span style="color:#007700">] = </span><span style="color:#DD0000">"index.php?page="</span><span style="color:#007700">;

</span><span style="color:#FF8000">// load the text for the requested page
</span><span style="color:#0000BB">$text </span><span style="color:#007700">= </span><span style="color:#0000BB">implode</span><span style="color:#007700">(</span><span style="color:#DD0000">''</span><span style="color:#007700">, </span><span style="color:#0000BB">file</span><span style="color:#007700">(</span><span style="color:#0000BB">$page </span><span style="color:#007700">. </span><span style="color:#DD0000">'.wiki.txt'</span><span style="color:#007700">));


</span><span style="color:#FF8000">// create a Wiki objext with the loaded options
</span><span style="color:#0000BB">$wiki </span><span style="color:#007700">= new </span><span style="color:#0000BB">Text_Wiki</span><span style="color:#007700">(</span><span style="color:#0000BB">$options</span><span style="color:#007700">);

</span><span style="color:#FF8000">// transform the wiki text.
</span><span style="color:#007700">echo </span><span style="color:#0000BB">$wiki</span><span style="color:#007700">-&gt;</span><span style="color:#0000BB">transform</span><span style="color:#007700">(</span><span style="color:#0000BB">$text</span><span style="color:#007700">);
</span><span style="color:#0000BB">?&gt;</span>

</span></code></pre>

<hr />
<h2 class="yawiki" id="toc22">Tables</h2>
<p class="yawiki">You can create tables using pairs of vertical bars:</p>


<pre class="yawiki"><code>|| cell one || cell two ||
|||| big ol' line ||
|| cell four || cell five ||
|| cell six || here's a very long cell ||</code></pre>



<table class="yawiki">
	<tr class="yawiki">

		<td class="yawiki">cell one</td>
		<td class="yawiki">cell two</td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki" colspan="2">big ol' line</td>
	</tr>
	<tr class="yawiki">

		<td class="yawiki">cell four</td>
		<td class="yawiki">cell five</td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki">cell six</td>
		<td class="yawiki">here's a very long cell</td>
	</tr>

</table>


<pre class="yawiki"><code>|| lines must start and end || with double vertical bars || nothing ||
|| cells are separated by || double vertical bars || nothing ||
|||| you can span multiple columns by || starting each cell ||
|| with extra cell |||| separators ||
|||||| but perhaps an example is the easiest way to see ||</code></pre>



<table class="yawiki">
	<tr class="yawiki">
		<td class="yawiki">lines must start and end</td>
		<td class="yawiki">with double vertical bars</td>

		<td class="yawiki">nothing</td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki">cells are separated by</td>
		<td class="yawiki">double vertical bars</td>
		<td class="yawiki">nothing</td>
	</tr>

	<tr class="yawiki">
		<td class="yawiki" colspan="2">you can span multiple columns by</td>
		<td class="yawiki">starting each cell</td>
	</tr>
	<tr class="yawiki">
		<td class="yawiki">with extra cell</td>
		<td class="yawiki" colspan="2">separators</td>

	</tr>
	<tr class="yawiki">
		<td class="yawiki" colspan="3">but perhaps an example is the easiest way to see</td>
	</tr>
</table>


</div>

<?php
}


function PrintStyles() {
?>
<style>

#fh {
	margin-top: 20px;
}

#fh p, #fh br, #fh td, #fh th {
	font-family: "Lucida Sans", Verdana, Arial, Geneva;
	font-size: 9pt;
	color: black;
}

#fh p { line-height: 150%; }

blockquote {
	border: 1px solid silver;
	background: #eee;
	margin: 4px;
	padding: 4px 12px;
}

table.admin {
	border: 2px solid #039;
	border-spacing: 0px;
	padding: 0px;
}

th.admin {
	padding: 4px;
	background: #039;
	color: white;
	font-weight: bold;
	vertical-align: bottom;
}

td.admin {
	border: 1px solid white;
	padding: 4px;
	background: #eee;
	vertical-align: top;
}

h2 { clear: both; }

#fh a:link, #fh a:active, #fh a:visited {
	color: blue;
	text-decoration: none;
	border-bottom: 1px solid blue;
}

#fh a:hover {
	color: blue;
	text-decoration: none;
	border-bottom: 1px dotted blue;
}


#fh li {
	margin-top: 3pt;
	margin-bottom: 3pt;
}

#fh pre {
	border: 1px dashed #036;
	background: #eee;
	padding: 6pt;
	font-family: ProFont, Monaco, Courier, "Andale Mono", monotype;
	font-size: 9pt;
}

input[type="text"], input[type="password"], textarea {
	font-family: ProFont, Monaco, Courier, "Andale Mono", monotype;
	font-size: 9px;
}

table.nav_table {
	width: 100%;
}

td.tabs_marginal {
	background: white;
	border-bottom: 1px solid black;
}

td.tabs_unselect {
	background: #aaa;
	border-top: 1px solid black;
	border-left: 1px solid black;
	border-right: 1px solid black;
	border-bottom: 1px solid black;
	text-align: center;
}

td.tabs_selected {
	background: #ddd;
	border-top: 1px solid black;
	border-left: 1px solid black;
	border-right: 1px solid black;
	border-bottom: none;
	text-align: center;
	font-weight: bold;
}

td.wide_marginal {
	background: #ddd;
	border-bottom: 1px solid black;
}

td.wide_unselect {
	background: #ddd;
	border-bottom: 1px solid black;
	text-align: center;
}

td.wide_selected {
	background: #ddd;
	border-bottom: 1px solid black;
	text-align: center;
	font-weight: bold;
}

td.tall_unselect {
	font-weight: normal;
}

td.tall_selected {
	font-weight: normal;
}

table.yawiki {
	border-spacing: 0px;
	border: 1px solid gray;
}

tr.yawiki {
}

th.yawiki {
	margin: 1px;
	border: 1px solid silver;
	padding: 4px;
	font-weight: bold;
}

td.yawiki {
	margin: 1px;
	border: 1px solid silver;
	padding: 4px;
}

th.yawiki-form {
	text-align: right;
	padding: 4px;
}

td.yawiki-form {
	text-align: left;
	padding: 4px;
}

legend.yawiki-form {
	font-size: 120%;
}

label.yawiki-form {
	font-weight: bold;
}

div.toc_list {
	border: 1px solid #ccc;
	background-color: #eee;
	padding: 1em;
	margin-bottom: 2em;
}

div.toc_item {
	font-size: 90%;
	margin-top: 0.5em;
}
</style>
<?php
}


PrintFooter();
?>