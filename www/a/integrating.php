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

++ Simple Example

The easiest way to embed a drawing is by inserting an <iframe> tag into your website. You will need to define the height and width of the iframe in your document.

http://$example_url/examples/cocc-iframe.html
<code type="php">
<iframe width="800" height="600" src="http://oregon.ctepathways.org/c/published/cocc_emt_basic" style="border:0px"></iframe>
</code>

This is the easiest method of embedding a drawing. It is also the most reliable, because having the drawing inside an iframe separates the CSS of your web page from the drawing itself. Otherwise it is possible that some CSS rules in your website would conflict with the drawing, causing undesired results.

For the same reason, this is also the most restrictive method. Because the drawing is inside an iframe, it is not possible to manipulate the CSS of the drawing. Links will be the browser's default blue color, and most other elements will have their default style.

++ Advanced Example

+++ Automatically adapting to the style of your website

By embedding the drawing using a <script> tag, this causes the browser to render the drawing within the same document as the containing website. Consequently, any CSS rules that apply to your document will also apply to the drawing. For example, the font of the text inside the boxes will match the font of your web page, and links in the drawing will be the same color as the links in your web page if you have a style defined for <a> tags.

http://$example_url/examples/cocc-javascript.html
<code type="php">
<div style="height:600px;">
	<script type="text/javascript" src="http://oregon.ctpathways.org/c/view.js?d=lcc_spanish"></script>
</div>
</code>

+++ Customizing the styles in the drawings

It is also possible, using CSS, to change the style of classes of objects within the drawings. For example, you can customize the size or color of headings.

[http://$example_url/examples/custom-h2.png]

In an external CSS file on your server, you can include custom CSS rules. When embedded through "view.js", all elements in the drawings are included inside a <div> element named "ctepathways." You can then reference all <h2> tags with the CSS rule ".ctepathways h2". The CSS code below would result in the previous image.
<code type="php">
<style type="text/css">
	.ctepathways h2 {
		font-size:14pt;
		font-weight: bold;
		color: #e5b42a;
	}
</style>
</code>


+++ Styling custom HTML in the box content

When editing the content of a box, click the "Source" button in the right side of the menu bar. You can enter any HTML inside this editor window, and it will be rendered inside the box. You can then enter custom <div> or <span> tags to further refine the control you have over the styles of individual parts of the box content. By writing HTML, you could even embed a table, or any other complex HTML code that you are unable to enter through the buttons provided. You can then write CSS rules to style the table appropriately.

[http://$example_url/examples/table-example.png]

Box Content:
<code type="php">
<table width="100%">
    <tbody>
        <tr class="row1">
            <td>One</td>
            <td>Two</td>
        </tr>
        <tr class="row2">
            <td>Three</td>
            <td>Four</td>
        </tr>
        <tr class="row1">
            <td>Five</td>
            <td>Six</td>
        </tr>
        <tr class="row2">
            <td>Seven</td>
            <td>Eight</td>
        </tr>
    </tbody>
</table>
</code>


In your web page, or an external CSS file on your server:
<code type="php">
<style type="text/css">
	.ctepathways .row1 {
		background-color: #ffffff;
	}
	.ctepathways .row2 {
		background-color: #f2e7ce;
	}
</style>
</code>


++ Completely Customized Drawings

Using further refined CSS rules, you can actually completely customize the look of boxes, lines and arrows. You can replace the images that make up the boxes with your own, giving you complete control over the look of the box. (Note: Do this at your own risk)

All object types are created by applying different CSS rules to a 3x3 table. The tables and code are provided below for your reference.

[[OBJECT_EXAMPLES]]

<code type="php">
Boxes:
<table cellspacing="0" cellpadding="0" class="box box_51739b" style="top: 160px; left: 40px; width: 360px;">
	<tbody>
		<tr>
			<td class="nw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="n">STARTING OPTIONS</td>
			<td class="ne"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="w"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="c"><p>Content</p></td>
			<td class="e"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="sw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="s"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="se"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
	</tbody>
</table>

Arrows:
<table cellspacing="0" cellpadding="0" class="arrow e arrow_9f0b31" style="top: 222px; left: 418px; height: 15px; width: 74px;">
	<tbody>
		<tr>
			<td class="nw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="n"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="ne"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="w"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="c"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="e"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="sw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="s"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="se"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
	</tbody>
</table>

Lines:
<table cellspacing="0" cellpadding="0" class="line v line_9f0b31" style="top: 358px; left: 572px; height: 84px; width: 15px;">
	<tbody>
		<tr>
			<td class="nw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="n"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="ne"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="w"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="c"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="e"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
		<tr>
			<td class="sw"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="s"><img src="http://$oregon_url/images/blank.gif"/></td>
			<td class="se"><img src="http://$oregon_url/images/blank.gif"/></td>
		</tr>
	</tbody>
</table>
</code>

The table that makes up a box is assigned two classes: "box," and "box_######" where the pound signs represent the hex color code that the box has been assigned. Similarly, this rule applies to arrows and lines. Writing a CSS rule for ".box" will apply to all boxes, while writing a rule for ".box_FF0000" will apply only to boxes that are defined to be red.

Lines and arrows have an additional CSS class assigned to them. Arrows can have the class n, s, e, or w depending on which direction the arrow is pointing. Lines can have either h or v, for horizontal or vertical. For a north-facing arrow, the inner table cell with the class "n" will have the background image that has the top section of the arrow. In order to completely customize the look, you would have to create a complete set of images for lines, arrows and boxes in each color that your school will be using.

The complete set of images necessary to recreate all the object types is given below. Pay particular attention to which parts of the images have transparency defined.
[[COMPONENT_IMAGES]]


By creating a new set of images for the components of the boxes and adding some CSS rules, you can completely customize their look. The link below illustrates some of the variations on box styles that are possible. Note that this links to an actual published drawing, the modifications necessary to achieve this are done only on the website that embeds the drawing.

http://$example_url/examples/box-styles.html

The CSS rules and image set to create the thin boxes with the light-colored title background are given below.

[[THIN_BOX_IMAGES]]

<code type="php">
<style type="text/css">
	.ctepathways .box_51739b td.s {
		background-image: url('http://$oregon_url/i/51739b/thinbox_s.png');
	}
	.ctepathways .box_51739b td.w {
		background-image: url('http://$oregon_url/i/51739b/thinbox_w.png');
	}
	.ctepathways .box_51739b td.e {
		background-image: url('http://$oregon_url/i/51739b/thinbox_e.png');
	}
	.ctepathways .box_51739b td.sw {
		background-image: url('http://$oregon_url/i/51739b/thinbox_sw.png');
	}
	.ctepathways .box_51739b td.se {
		background-image: url('http://$oregon_url/i/51739b/thinbox_se.png');
	}
	.ctepathways .box_51739b td.ne {
		background-image: url('http://$oregon_url/i/51739b/thinbox_ne.png');
	}
	.ctepathways .box_51739b td.nw {
		background-image: url('http://$oregon_url/i/51739b/thinbox_nw.png');
	}
	.ctepathways .box_51739b td.n {
		background-image: url('http://$oregon_url/i/51739b/thinbox_n.png');
		color: #51739b;
		text-transform: lowercase;
	}
</style>
</code>

The link below shows what this drawing looks like without the custom CSS rules.

http://$example_url/examples/box-styles-normal.html

The rest of the images needed to recreate all the styles on the page are given below.

[[OTHER_BOX_IMAGES]]


<?php
$text = ob_get_contents();
ob_end_clean();

$wiki =& new Text_Wiki();
$xhtml = $wiki->transform($text, 'Xhtml');

$xhtml = str_replace("\n".'<span style="color: #0000BB">&lt;?php'."\n\n",'<span>',$xhtml);
$xhtml = str_replace('?&gt;','',$xhtml);


ob_start();
?>
<style type="text/css">
	.imagelist td {
		background-color: #999999;
		vertical-align: bottom;
	}
</style>
<?php
$T = new WrappingTable();
$T->cols = 8;
$images = '
arrn_nw.png
arrn_n.png
arrn_ne.png
arre_ne.png
arre_e.png
arre_se.png
arrs_sw.png
arrs_s.png
arrs_se.png
arrw_nw.png
arrw_w.png
arrw_sw.png
cap_n.png
cap_e.png
cap_s.png
cap_w.png
tbox_nw.png
tbox_n.png
tbox_ne.png
tbox_w.png
tbox_e.png
tbox_sw.png
tbox_s.png
tbox_se.png
';
foreach( explode("\n",$images) as $c ) {
	if( $c != "" ) {
		$T->AddItem('<img src="/i/000000/'.$c.'"><br>'.$c);
	}
}
echo '<div class="imagelist">';
$T->Output();
echo '</div>';
$image_table = ob_get_contents();
ob_end_clean();




ob_start();
$T = new WrappingTable();
$T->cols = 8;
$images = '
thinbox_nw.png
thinbox_n.png
thinbox_ne.png
thinbox_w.png
thinbox_e.png
thinbox_sw.png
thinbox_s.png
thinbox_se.png
';
foreach( explode("\n",$images) as $c ) {
	if( $c != "" ) {
		$T->AddItem('<img src="/i/000000/'.$c.'"><br>'.$c);
	}
}
echo '<div class="imagelist">';
$T->Output();
echo '</div>';
$thin_table = ob_get_contents();
ob_end_clean();



ob_start();
$T = new WrappingTable();
$T->cols = 8;
$images = '
squarebox_nw.png
squarebox_ne.png
squarebox_sw.png
squarebox_se.png
chiseledbox_nw.png
chiseledbox_ne.png
chiseledbox_sw.png
chiseledbox_se.png
dashedbox_w.png
dashedbox_s.png
dashedbox_e.png
';
foreach( explode("\n",$images) as $c ) {
	if( $c != "" ) {
		$T->AddItem('<img src="/i/000000/'.$c.'"><br>'.$c);
	}
}
echo '<div class="imagelist">';
$T->Output();
echo '</div>';
$other_table = ob_get_contents();
ob_end_clean();




ob_start();
?>
<style type="text/css">
	.object_examples table.examples {
		background-color: #999999;
	}
	.object_examples table.examples td {
		text-align: center;
		vertical-align: middle;
		border: 1px #CCCCCC solid;
		padding: 3px;
	}

</style>
<div class="object_examples">
<table width="400"><tr>
<td>
	<table cellspacing="0" class="examples">
		<tbody>
			<tr>
				<td class="nw"><img src="http://$oregon_url/i/000000/tbox_nw.png"/></td>
				<td class="n"><img src="http://$oregon_url/i/000000/tbox_n.png"/></td>
				<td class="ne"><img src="http://$oregon_url/i/000000/tbox_ne.png"/></td>
			</tr>
			<tr>
				<td class="w"><img src="http://$oregon_url/i/000000/tbox_w.png"/></td>
				<td class="c">&nbsp;</td>
				<td class="e"><img src="http://$oregon_url/i/000000/tbox_e.png"/></td>
			</tr>
			<tr>
				<td class="sw"><img src="http://$oregon_url/i/000000/tbox_sw.png"/></td>
				<td class="s"><img src="http://$oregon_url/i/000000/tbox_s.png"/></td>
				<td class="se"><img src="http://$oregon_url/i/000000/tbox_se.png"/></td>
			</tr>
		</tbody>
	</table>
</td><td>
	<table cellspacing="0" class="examples">
		<tbody>
			<tr>
				<td class="nw"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="n"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="ne"><img src="http://$oregon_url/i/000000/arre_ne.png"/></td>
			</tr>
			<tr>
				<td class="w"><img src="http://$oregon_url/i/000000/cap_w.png"/></td>
				<td class="c"><img src="http://$oregon_url/i/000000/solidline_h.png"/></td>
				<td class="e"><img src="http://$oregon_url/i/000000/arre_e.png"/></td>
			</tr>
			<tr>
				<td class="sw"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="s"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="se"><img src="http://$oregon_url/i/000000/arre_se.png"/></td>
			</tr>
		</tbody>
	</table>
</td><td>
	<table cellspacing="0" class="examples">
		<tbody>
			<tr>
				<td class="nw"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="n"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="ne"><img src="http://$oregon_url/images/blank.gif"/></td>
			</tr>
			<tr>
				<td class="w"><img src="http://$oregon_url/i/000000/cap_w.png"/></td>
				<td class="c"><img src="http://$oregon_url/i/000000/solidline_h.png"/></td>
				<td class="e"><img src="http://$oregon_url/i/000000/cap_e.png"/></td>
			</tr>
			<tr>
				<td class="sw"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="s"><img src="http://$oregon_url/images/blank.gif"/></td>
				<td class="se"><img src="http://$oregon_url/images/blank.gif"/></td>
			</tr>
		</tbody>
	</table>
</td>
</tr></table>

</div>
<?php
$object_examples = ob_get_contents();
ob_end_clean();



$xhtml = str_replace("[[COMPONENT_IMAGES]]",$image_table,$xhtml);
$xhtml = str_replace("[[THIN_BOX_IMAGES]]",$thin_table,$xhtml);
$xhtml = str_replace("[[OTHER_BOX_IMAGES]]",$other_table,$xhtml);
$xhtml = str_replace("[[OBJECT_EXAMPLES]]",$object_examples,$xhtml);

$xhtml = str_replace("\$example_url", "examples.ctepathways.org", $xhtml);

$xhtml = str_replace("\$oregon_url", "oregon-test.ctepathways.org", $xhtml);
//$xhtml = str_replace("\$oregon_url", "pathways", $xhtml);

echo $xhtml;



PrintFooter();
?>