<?php
chdir("..");
include("inc.php");
require_once "Text/Wiki.php";

ModuleInit('integrating');

PrintHeader();

ob_start();
?>

[[toc]]

----

++Introduction

The purpose of this document is to provide some examples of embedding Career Pathways drawings into existing web sites. Several examples are provided to illustrate the different possibilities for integrating these drawings.


++ Embedding Published Drawings

You can embed a drawing by inserting an <iframe> tag into your website. You will need to define the height and width of the iframe in your document.

<code type="php">
<iframe width="800" height="600" src="http://oregon.ctepathways.org/c/published/cocc_emt_basic" frameborder="0" scrolling="no"></iframe>
</code>

You can find the URL for this drawing on the Drawing Info page. It will always look like this: "http://oregon.ctepathways.org/c/published/drawing_code"

This is the best method of embedding a drawing. When a new version of this drawing is published, this link will link to the new version. This link will always link to the published version of the drawing specified by the drawing code, in this case, "cocc_emt_basic".

Note: If you ever change the name of the drawing, the drawing code will change as well, breaking any links on external sites to this drawing. For this reason, it is a good idea never to change the names of drawings once they are used in external sites.


++ Embedding an Unpublished Version

If you wish, you can also embed an unpublished version of a drawing. In this case, you provide the version number in addition to the drawing code. You can find the full URL to include on the Version Info page.

<code type="php">
<iframe width="800" height="600" src="http://oregon.ctepathways.org/c/version/cocc_emt_basic/3.html" style="border:0px"></iframe>
</code>

When embedding a drawing this way, you will ALWAYS be linking to this specific version. If a new version is created and published, this content will not be modified. This has the disadvantage of not automatically updating when a new version is created. This method is not recommended except for special cases.

++ Colors

Here is a list of the colors used throughout the Career Pathways Web Tool.

$color_chart

<?php
$text = ob_get_contents();
ob_end_clean();


$colors = array('999999','cccccc','cf9d2b','e6d09e','295a76');
$color_chart = '<table style="margin-left:20px">';
foreach( $colors as $c ) {
	$color_chart .= '<tr><td width="30" height="20" style="background-color:#'.$c.'"></td><td>#'.strtoupper($c).'</td></tr>';
}
$color_chart .= '</table>';




$wiki =& new Text_Wiki();$xhtml = $wiki->transform($text, 'Xhtml');

$xhtml = str_replace("\n".'<span style="color: #0000BB">&lt;?php'."\n\n",'<span>',$xhtml);$xhtml = str_replace('?&gt;','',$xhtml);

$xhtml = str_replace("\$example_url", "examples.ctepathways.org", $xhtml);
$xhtml = str_replace("\$oregon_url", "oregon-test.ctepathways.org", $xhtml);
$xhtml = str_replace("\$color_chart", $color_chart, $xhtml);

echo $xhtml;


PrintFooter();
?>