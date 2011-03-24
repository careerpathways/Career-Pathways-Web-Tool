<?php
header("Content-type: text/css");

$WHITE = "FFFFFF";
$BLACK = "000000";
$GREY = "999999";
$LT_GREY = "CCCCCC";
$D_GREY = "333333";
$BLUE = "003366";
$D_BLUE = "001133";

$GOLD = "e6d09e";
$D_GOLD = "cf9d2b";
$L_GOLD = "f2e7ce";

$RED = "EE3300";

?>

body
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 8pt;
  color:#<?= $BLACK; ?>;
  margin: 0px;
}

#sandbox_border {
	position: fixed;
	width: 100%;
	background: url(/images/sandbox.gif) repeat-x;
	background-color: #FF0000;
	z-index: 5;
	height: 15px;
}

h1
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 13pt;
  color:#<?= $D_GREY; ?>;
  margin: 0px;
}

h2
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 13pt;
  color:#<?= $D_GOLD; ?>;
  margin: 0px;
}
.h2
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 13pt;
  color:#<?= $D_GOLD; ?>;
}


h3
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 10pt;
  color:#<?= $D_GREY; ?>;
  margin-bottom: 2px;
}

h4
{
  font-family: verdana, tahoma, arial, sans-serif;
  font-size: 10pt;
  color:#<?= $GREY; ?>;
}

a, a:visited {
  color:#<?= $D_BLUE; ?>;
}
a:hover, a:visited:hover {
  color:#<?= $GREY; ?>;
}

a.noline {
	text-decoration: none;
}


hr
{
  border-top: 1px #<?= $D_GREY ?> solid;
  border-left: 0px;
  border-right: 0px;
  border-bottom: 0px;
  padding-bottom: 5px;
  margin: 0px;
  margin-top: 5px;
}

img {
  border-width:0px;
}

input
{
  font-family:verdana, arial, sans-serif;
  font-size:8pt;
  border:1px #<?= $GREY ?> solid;
}
input.submit
{
  background-color:#<?= $D_GREY ?>;
  color:#FFFFFF;
}
input.submit_disabled
{
  background-color:#666666;
  color:#CCCCCC;
}
input.submit, input.submit_disabled {
  padding-left: 4px;
  padding-right: 4px;
  padding-top: 1px;
  padding-bottom: 1px;
}
input.checkbox, input.radio
{
  border-style:none;
  border-width:0px;
}
input.image
{
  border-style:none;
  border-width:0px;
}

textarea
{
  font-family:verdana, arial, sans-serif;
  font-size:8pt;
  border-style:solid;
  border-width:1px;
  border-color:#<?= $GREY ?>;
}

select
{
  font-family:verdana, arial, sans-serif;
  font-size:8pt;
  border-style:solid;
  border-width:1px;
  border-color:#<?= $GREY ?>;
}

.border, iframe {
  border:1px #<?= $GREY ?> solid;
}

.small
{
  font-size:8pt;
}

.tiny {
  font-size:9px;	
}

.indent {
	margin-left: 30px;
}
.smallindent {
	margin-left: 10px;
}

.navtext
{
  padding-left:28px;
}

.grey {
	color: #999999;
}


.index
{
  color:#FFFFFF;
}

table.template
{
  border: 1px #FFFFFF solid;
  border-collapse: collapse;
  border-spacing: 0px;
}

table
{
  border-spacing: 0px;
  border-collapse: collapse;
}

td.noborder
{
  border: 0px;
}

td
{
  border: 0px;
  font-size:8pt;
}

td.border, table.bordered td, table.bordered th
{
  border: 1px #<?= $GREY; ?> solid;
  padding:3px;
}

th
{
  font-weight:bold;
  color:#<?= $BLUE; ?>;
  text-align: left;
  vertical-align: top;
}

td.form
{
  border: 0px #<?= $GREY; ?> solid;
  padding:5px;
  background-color:#FFFFFF;
}

td.list
{
  font-size:8pt;
  border: 0px;
  padding:2px;
  padding-right:20px;
}


td.head
{
  border: 1px #<?= $GREY; ?> solid;
  background-color:#<?= $LT_GREY ?>;
  color:#000000;
  padding:5px;
  font-weight:bold;
}

td.multidate
{
  padding: 2px;
}

textarea.code 
{
	font-family: courier new, courier, sans-serif;
}


.row0 { 
	background-color: #DDDDDD;
}
.row1 {
	background-color: #FFFFFF;
}

tr.drawing_main {
    background-color: #<?= $GOLD ?>;
}
tr.published {
	background-color: #<?= $L_GOLD ?>;
}


.linkhead
{
  font-size:11pt;
  color:#<?= $GREY; ?>;
  font-weight:bold;
}

.linksubhead
{
  font-size:8pt;
  color:#<?= $BLUE; ?>;
  font-weight:bold;
  text-decoration: none;
}

a.linksubhead
{
  color:#<?= $BLUE; ?>;
}

a.linksubhead:hover
{
  color:#<?= $D_GREY; ?>;
}



a.edit, a.edit:visited {
  font-weight:normal;
  color:#<?= $D_GREY; ?>;
  text-decoration: none;
}

a.edit:hover, a.edit:visited:hover {
	color: #777777;
}


.publish_link, .publish_link_inactive {
	text-decoration: none;
	font-size: 14pt;
	padding: 3px;
}

.publish_link {
	border: 1px #<?= $D_GOLD ?> solid;
	background-color: #<?= $L_GOLD ?>;
}
.publish_link_inactive {
	border: 1px #666666 solid;
	background-color: #<?= $LT_GREY ?>;
	color: #666666;
}

.error {
	color: red;
}




#header {
	height:61px;
	background-color: white;
}

#topbar {
	background-color: #444444;
	width: 100%;
	text-align: right;
}
#topbar_inside {
	color: white;
	padding-right: 30px;

}
#topbar a {
	text-decoration: none;
	color: white;
}
#topbar a:hover {
	color: #<?= $GOLD ?>;
}

#navbox {
	background: url("/images/navbox-br.gif") bottom right;
	background-repeat: no-repeat;	
	background-color: #<?= $GOLD ?>;
	width: 170px;
}

.links
{

}

.links ul {
	margin: 0px;
	padding: 0px;
	list-style-type: none;
	padding-top: 20px;
}

.links ul li {
	padding-bottom: 4px;
}

.links a {
	text-decoration: none;	
	display: block;
	padding-left: 10px;
	color: black;
}
.links a:hover {
	background-color: white;
	color: black;
}
.links a:visited {
	color: black;
}		
.links a:visited:hover {
	background-color: white;
	color: black;
}
.links li.active a {
	background-color: #<?= $L_GOLD ?>;
}

#sideboxes {
	float: left;
}

#main {
	position: absolute;
	top: 74px;
	left: 180px;
	margin-right: 30px;
}

#main-c {
	border-left: 1px #<?= $D_GOLD ?> solid;
	border-right: 1px #<?= $D_GOLD ?> solid;
	border-bottom: 1px #<?= $D_GOLD ?> solid;
	min-height: 300px;
	min-width: 840px;
	padding: 0px;
}
#main-c-in {
	padding: 5px;
}
#main-b {
	position: relative;
	background: url("/images/outline-bl.gif") bottom left;
	background-repeat: no-repeat;	
	height: 30px;
	margin-bottom: 1px;
}
#main-br {
	background: url("/images/outline-br.gif") bottom right;
	background-repeat: no-repeat;	
	height: 30px;
}

#module_name {
	color: #<?= $BLACK ?>;
	background-color: #<?= $GOLD ?>;
	font-size: 19px;
	padding-top: 2px;
	padding-bottom: 2px;
	padding-left: 5px;
	letter-spacing: 3px;
}



#title_value, .drawing_title
{
  color: #<?= $D_GREY ?>;
}
.version_title
{
  font-size: 13pt;
  color: #<?= $D_GOLD ?>;
}



.crosshair area {
	cursor: crosshair;
}


.row_light {
	background-color: #FFFFFF;
}
.row_dark {
	background-color: #e4e4e4;
}
.row_hilite {
	background-color: #FFFFBB;
}


.table_title {
	font-size: 11pt;
	color: #<?= $D_GOLD ?>;
	font-weight: bold;
}

.header_cell {
	font-weight: bold;
}
.header_cell, .content_cell {
	border: 1px #CCCCCC solid;
}
.user_table {
	margin-bottom: 20px;
}

a.chart_header {
	text-decoration: none;
}

#toolbar, #helpbar, #infobar, #resourcebar {
	background: url("/images/navbox-br.gif") bottom right;
	background-repeat: no-repeat;	
	background-color: #<?= $GOLD ?>;
	width: 170px;
}
#toolbar, #helpbar, #infobar, #resourcebar, #navbox {
	margin-bottom: 20px;
}

#toolbar_header, #helpbar_header, #infobar_header, #resourcebar_header {
	background-repeat: no-repeat;
	background-color: #<?= $D_GOLD ?>;
	height: 30px;
}
#toolbar_header {
	background-image: url("/images/toolbar-head.gif");
}
#helpbar_header {
	background-image: url("/images/helpbar-head.gif");
}
#infobar_header {
	background-image: url("/images/infobar-head.gif");
}
#resourcebar_header {
	background-image: url("/images/resources-head.gif");
}

#toolbar_content, #helpbar_content, #infobar_content {
	margin: 10px;
	padding-bottom: 10px;
}

#toolbar a.toolbarButton {
	text-decoration: none;	
	display: block;
	padding: 5px;
	margin: 2px;
	border: 1px #<?= $D_GOLD ?> solid; 
}
#toolbar a.publish:hover {
	background-color: white;	
	color: black;
}

#helpbar p {
	margin-top: 6px;
	margin-bottom: 6px;
}

#helpbar a {
	text-decoration: none;
}

#drawing_canvas {
	background: url('/c/grid.png');
	position: absolute;
	left: 180px;
	top:74px;
	height: 1200px;
	width: 1600px;
}

#search_box {
  background: url(/common/silk/find.png) left no-repeat;
  padding-left: 16px;
}

.school_color_box {
	height:40px;
	width:40px;
	text-align:right;
}

.school_color_box_mini, .school_color_box_small {
	margin-right: 2px;
	float:left;
}

.school_color_box_mini {
	height:12px;
	width:12px;
}
.school_color_box_small {
	height:24px;
	width:24px;
}

.school_color_grp {
	display: none;
}


.school_color_x {
	background-color:white;
	padding-left:2px;
	padding-right:2px;
	padding-bottom: 1px;
	text-decoration: none;
}


#hiddenContainer {
	display: none;
}


.news_header {
	font-size: 13px;
	font-weight: bold;
	color: #<?= $D_GOLD ?>;
	letter-spacing: 2px;
	border-bottom: 1px #<?= $D_GREY ?> solid;
}

.red {
	color: #<?= $RED ?>;
}

.news_date {
	text-align: right;
	letter-spacing: 1px;
	font-size: 10px;
}

#helplink {
	text-align: right;
	margin-right: 30px;
	font-size: 9px;
}
#helplink a {
	text-decoration: none;
	color: #999999;
}
#helplink a:hover {
	color: #AAAAAA;
}

.version_list_published {
	background-color: #<?= $L_GOLD ?>;
}


.imglinkadjust {
	margin-bottom: 5px;	
}


@media print {
	#toolbar, #helpbar, #infobar, #navbox {
		display: none;
	}
	#main {
		position: relative;
		top: 0px;
		left: 0px;	
	}
	#main-c {
		border: 0px;
		width: 100%;	
		min-width: 100%;
	}
}

.editorWindow #mceBox {
	width: 600px;
	height: 300px;
	margin-left: auto;
	margin-right: auto;
}

.editorWindow .fckOK {
	background-color: #<?= $L_GOLD ?>;
	border: 1px #<?= $D_GOLD ?> solid;
	padding: 4px;
	text-align: center;
	width: 70px;
	margin-top: 3px;
	margin-left: auto;
	margin-right: auto;
}


#live_lists {
	width: 100%;
	border: 1px #<?= $D_GOLD ?> solid;
	margin-bottom: 4px;
}

#live_lists .live_list {
	overflow:auto;
	border: 1px #<?= $D_GOLD ?> solid;
}

#live_lists .ajaxloader {
	float: right;
	width: 16px;
	height: 16px;
}

#live_lists .title {
	display: block;
	background-color: #<?= $D_GOLD ?>;
	padding: 2px;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 4px;
}

#live_lists ul {
	list-style-type: none;
	margin: 0px;
	padding: 0px;
}

#live_lists ul li {
	margin: 0px;
	padding: 0px;
}

#live_lists .odd {
	background-color: #eeeeee;
}

#live_lists ul li a {
	text-decoration: none;
	display: block;
	padding-left: 16px;
	color: black;
}

#live_lists ul li a:hover {
	background-color: #<?= $L_GOLD ?>;
}

#live_lists select {
	width: 100%;
	border: 0px;
}

#dpcontainer {
	background-color: #FFFFFF;
	width: 800px;
	height: 600px;
	margin-left: auto;
	margin-right: auto;
}

#dpcontainer iframe {
	width: 800px;
	height: 600px;
}

#copyPopup {
	background-color: #ffffff;
	width: 400px;
	height: 300px;
	margin-left: auto;
	margin-right: auto;
}

#copyPopup iframe {
	width: 400px;
	height: 300px;
}

.drawinglist_name {
	font-size: 1.2em;
}
#drawing_list a {
	text-decoration: none;
}

.fwfont {
  font-family: lucida console, monaco;
  font-size: 8pt;
  white-space: pre;
}


#greybox {
	position: absolute;
	z-index: 1000;
	top: 0px;
	left: 0px;
	background: url("/images/greybox-overlay.png");

}

#greybox table {
	border-collapse: collapse;	
}
#greybox td {
	border: 0px;
	margin: 0px;
	padding: 0px;
}

#greybox_inset {
	margin-left: auto;
	margin-right: auto;
}

#greybox_container {
	background-color: #<?= $GOLD ?>;
}

#greybox_bottom {
	height: 30px;	
}

#greybox_bottomleft {
	background-color: #<?= $GOLD ?>;
}

#greybox_bottomright {
	background: url("/images/greybox-corner.png") bottom right;
	background-repeat: no-repeat;	
	height: 30px;
	width: 30px;
}

#greybox_clear {
	clear: both;	
}

#greybox_xbutton {
	text-align: right;
	margin-right: 15px;
}

#greybox_xbutton a {
	font-size: 18pt;
	text-decoration: none;
}

#greybox_contents {
  margin-left: 15px;
  margin-right: 15px;
  background-color: white;
}



#search_form {
	margin-bottom: 5px;
}
#search_form input.submit {
	font-size:9px;
}


#my_drawings_link {
	text-align:right;
}
#my_drawings_link a {
	border: 1px #<?= $GOLD ?> solid;
	padding: 2px;
	background-color: #<?= $D_GOLD ?>;
	color: #FFFFFF;
}
#my_drawings_link a:hover {
	color: #<?= $GOLD ?>;
}



.login_button {
	border: 1px #000000 solid;
	background-color: #<?= $D_GOLD ?>;
	padding-top: 3px;
	padding-bottom: 2px;
	padding-right: 8px;
	padding-left: 8px;
}

.login_button a {
	color: white;
	text-decoration: none;
	font-size: 11px;
}

.login_button a, .login_button a:hover,.login_button a:visited, .login_button a:visited:hover {
	color: white;
}

.button_link {
	padding-top: 3px;
	padding-bottom: 2px;
	padding-right: 8px;
	padding-left: 8px;
	border: 1px #<?= $GREY ?> solid;
	background-color: #<?= $D_GREY ?>;
}
.button_link a, .button_link a:hover {
	color: white;
	text-decoration: none;
}



.pager_links {
	border-top: 1px #<?= $GOLD ?> dashed;
	border-bottom: 1px #<?= $GOLD ?> dashed;
}
.pager_links a {
	text-decoration: none;
}
.pager_links .active {
	color: #<?=$D_GOLD?>;
}


.percent_bar {
	border: 1px #<?=$D_GOLD?> solid;
	height: 13px;
}

.percent_inside {
	background-color: #<?= $GOLD ?>;
	height: 13px;
}

.log_scrollbox {
	overflow:auto; 
	border: 1px #cf9d2b solid;
}


#browserNotice {
    width: 250px;
	float: right;
	margin-bottom: 20px;
	padding: 6px;
	border: 4px #CC6666 solid;
}


#dash_links {
	width: 200px;
	border: 1px #<?= $D_GOLD ?> solid;
	float: right;
}
#dash_links_title {
	background-color: #<?= $D_GOLD ?>;
	text-align: center;
	text-transform: uppercase;
	color: white;
	font-weight: bold;
	padding-bottom: 2px;
}
#dash_links ul {
	list-style-type: none;
	margin: 0px;
	padding: 0px;
}
#dash_links li {
	background-color: #efefef;
	margin-top: 10px;
	text-align: center;
}
#dash_links a {
	text-decoration: none;
	font-size: 10pt;
	display: block;
	padding-top: 8px;
	padding-bottom: 8px;
	color: black;
}
#dash_links a:hover {
	background-color: #<?= $GOLD ?>;
	color: black;
}


.dlist_link {
  margin-right: 10px;
}




.post_mini_full
{
  background-color: #CCCCCC;
}
.post_chart_mini 
{
  width: 300px;
}
.post_chart_mini td, .post_chart_mini th
{
  border: 1px #999999 solid !important;
}
.post_chart_mini .post_head_row
{
  width: 14px;
  text-align: center;
  font-size: 8px;
}
.post_chart_mini .post_cell
{
  height: 24px;
}
.post_chart_mini .post_head_main, .post_chart_mini .post_footer
{
  height: 5px;
}
.post_chart_mini .post_sidebar_left, .post_chart_mini .post_sidebar_right
{
  width: 2px;
}

.post_large_number
{
  font-size: 11pt;
}

.post_import_preview input {
  margin-bottom: 6px;
}
.post_import_preview .hr {
  margin-bottom: 20px;
  padding-top: 10px;
  border-bottom: 1px #<?= $D_GREY ?> solid;
}
ol.import_instructions 
{
  list-style-type: none;
  margin: 0 auto 30px auto;
  padding: 0;
  width: 500px;
}
ol.import_instructions > li
{
  margin: 0;
  padding: 15px 0 15px 40px;
}

.import_instructions li.l1 {
  background: url(/images/numbers/01.png) left no-repeat;
}
.import_instructions li.l2 {
  background: url(/images/numbers/02.png) left no-repeat;
}
.import_instructions li.l3 {
  background: url(/images/numbers/03.png) left no-repeat;
}
.import_instructions li.l4 {
  background: url(/images/numbers/04.png) left no-repeat;
}

.drawing_select_hover {
  background-color: #FFFFAA;
}

#connectionForm #submit_btn {
  border: 1px #999999 solid;
  background-color: #<?= $D_GOLD ?>;
  font-size: 13pt;
  width: 70px;
  text-align: center;
  margin-left: auto;
  margin-right: 0;
  margin-top: 4px;
}
#connectionForm #submit_btn:hover {
  cursor: pointer;
}

#drawing_form #num_columns, #drawing_form #num_terms, #drawing_form #num_extra_rows
{
  width: 50px;
}

tr.editable, tr.even {
	background-color:#<?=$L_GOLD?>;
}

/* stats page */
.olmis_title {
  margin-top: 8px;
  font-size: 1.2em;
}
.olmis_roadmap {
  margin-left: 30px;
}

.drawing_schoolname {
    background-color: #<?= $BLUE ?>;
	color: white;
}
.drawinglist_schoolname {
	font-size: 1.2em;
	font-weight: bold;	
	padding: 4px;
}


.trim {
	overflow: hidden;
	white-space: nowrap;
}
.external_links .trim {
	width: 600px;
}
.external_links .make_primary {
	cursor: pointer; 
	width: 16px; 
	height: 16px; 
}
.external_links .make_primary {
	background: url(/common/silk/link_go.png) no-repeat -16px 0;
}
.external_links .make_primary.primary {
	background: url(/common/silk/link_break.png) no-repeat 0 0;
}
.external_link.primary {
	background-color:#<?=$L_GOLD?>;
}


