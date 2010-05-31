<?php
chdir("..");
include("inc.php");

ModuleInit('schoolcolors');


if( IsAdmin() ) {
	if( Request('school_id') ) {
		$school_id = $_REQUEST['school_id'];
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}


$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
$TEMPLATE->addl_scripts[] = '/files/greybox.js';

PrintHeader();
ShowSchoolForm($school_id);
PrintFooter();





function ShowSchoolForm($id="") {
global $DB;

$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=$id");
if( !is_array($school) ) {
	echo 'Specified record does not exist.';
	return false;
}

?>
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br>
<br>


<script type="text/javascript" src="http://yui.yahooapis.com/2.4.1/build/yahoo-dom-event/yahoo-dom-event.js" ></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.4.1/build/dragdrop/dragdrop-min.js" ></script>

<script type="text/javascript" src="http://yui.yahooapis.com/2.4.1/build/utilities/utilities.js" ></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.4.1/build/slider/slider-min.js" ></script>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.4.1/build/colorpicker/assets/skins/sam/colorpicker.css">
<script type="text/javascript" src="http://yui.yahooapis.com/2.4.1/build/colorpicker/colorpicker-beta-min.js"></script>

<script type="text/javascript">

var Event = YAHOO.util.Event;
var DDM = YAHOO.util.DragDropMgr;

var school_id = <?= $school['id'] ?>;
var dragged_x; var dragged_y;
var colorPicker;
var currentColorTable;

var curColorsLoaded = false;
var webColorsLoaded = false;
var yuiLoaded = false;

function switch_school(id) {
	document.location.href = "/a/schoolcolors.php?school_id="+id;
}

function deleteColor(school_id, color, replaceWith) {
	if(typeof(replaceWith) == "undefined" && parseInt(jQuery("#color_cell_"+color+" .school_color_num").html()) > 0){ 
		ajaxCallback(function(data){
		    chGreybox.create(data, 400, 300, null, "Reassign Color");
		}, '/a/schoolcolors_action.php?promptDelete&color='+color+'&sid='+school_id);
	}else{
		ajaxCallback(receivedCurrentColors, '/a/schoolcolors_action.php?delete&color='+color+'&sid='+school_id+'&replaceWith='+replaceWith);
	}
}

function getColors(school_id) {
	ajaxCallback(receivedCurrentColors, '/a/schoolcolors_action.php?sid='+school_id);
}

function fetchColors(mode) {
	if( mode == 'website' ) {
		var website = getLayer('school_website').value;
		getLayer('auto_colors').innerHTML = '<img src="/images/spin-loader.gif">';
		ajaxCallback(receivedAutoColor, '/a/schoolcolors_autocolor.php?website='+website);
	} else {
		ajaxCallback(receivedAutoColor, '/a/schoolcolors_autocolor.php?blank');
	}
}

function receivedAutoColor(colors) {
	var data = eval(colors);

	var autocolors = data.colors;
	var container = getLayer('auto_colors');

	showColorList(autocolors, container, 'auto');

	if( data.request_mode == 'request' ) {
		if( autocolors.length > 0 ) {
			getLayer('auto_color_help').innerHTML = 'Drag the desired colors onto the "Current Colors" list.';
		} else {
			getLayer('auto_color_help').innerHTML = 'No colors were found or unable to parse website. Enter colors using the Color Picker below.';
		}
	}
	
	webColorsLoaded = true;
	loadPicker();
}

function receivedCurrentColors(obj) {
	var data = eval(obj);
	var container = getLayer('school_colors');

	showColorList(data.colors, container, 'current', data.usage);

	curColorsLoaded = true;
	loadPicker();

	chGreybox.close(); // only relevant when the replace prompt is open
}

function showColorList(colors, container, mode, usage) {
	// mode is either 'auto' or 'current'

	var cl_tbody = document.createElement('tbody');
	var cl_table = document.createElement('table');
	var wrap_num = 12;  // wrapping doesn't work very well, so hopefully it will never happen with n=18

	var cl_row;
	for( var i=0; i<colors.length; i++ ) {
		if( i % wrap_num == 0 ) {
			cl_row = document.createElement('tr');
			cl_tbody.appendChild(cl_row);
		}

		var cl_cell = document.createElement('td');
			cl_cell.className = 'content_cell';
			cl_cell.id = 'color_cell_' + colors[i];
			cl_cell.width = '40px';

		var cl_color = document.createElement('div');
			cl_color.title = '#' + colors[i];
			cl_color.style.backgroundColor = '#' + colors[i];
			cl_color.className = 'school_color_box';
			if( mode == 'auto' ) {
				cl_color.autoColorValue = colors[i];
				cl_color.id = 'autocolor_' + colors[i];
				cl_color.parentCell = cl_cell;
			}

		if( mode == 'current' && colors[i] != '333333' && colors[i].toLowerCase() != 'ffffff' ) {
			cl_xbutton = document.createElement('a');
			cl_xbutton.className = "school_color_x";
			cl_xbutton.href = "javascript:deleteColor("+school_id+",'"+colors[i]+"')";
			cl_xbutton.innerHTML = 'x';
			cl_color.appendChild(cl_xbutton);
		}

		cl_cell.appendChild(cl_color);

		if(typeof(usage) != "undefined"){
			cl_usage = document.createElement('div');
			cl_usage.className = "school_color_num";
			cl_usage.innerHTML = usage[i];
			cl_cell.appendChild(cl_usage);
		}
		
		cl_row.appendChild(cl_cell);

		if( mode == 'auto' ) {
			var cl_color_drag = new autoColorDD('autocolor_' + colors[i]);
		}
	}
	
	/* fill the remaining empty cells with <td>s so it draws a border */
	if( colors.length % wrap_num > 0 ) {
		for( var j = colors.length % wrap_num; j < wrap_num; j++ ) {
			var cl_cell = document.createElement('td');
				cl_cell.className = 'content_cell';
				cl_cell.width = '40px';
			cl_row.appendChild(cl_cell);
		}
	}

	cl_table.appendChild(cl_tbody);

	/*
	if( mode == 'current' ) {
		var cl_cell = document.createElement('td');
			cl_cell.className = 'content_cell';
			cl_cell.innerHTML = '&nbsp;';
			cl_cell.width = '40px';
		// there will always be a cl_row object because #333333 will always be returned even if there are no colors
		cl_row.appendChild(cl_cell);
	}
	*/

	if( mode == 'auto' && colors.length == 0 ) {
		var cl_row = document.createElement('tr');
		cl_tbody.appendChild(cl_row);
		cl_table.appendChild(cl_tbody);
		for( var j=0; j<8; j++ ) {
			var cl_cell = document.createElement('td');
				cl_cell.className = 'content_cell';
				cl_cell.innerHTML = '&nbsp;';
				cl_cell.width = '40px';
			cl_row.appendChild(cl_cell);
		}
	}

	container.innerHTML = '';
	container.appendChild(cl_table);

	if( mode == 'current' ) {
		currentColorTable = cl_table;
	}
}




function initPage() {
	//curColorsLoaded = true;
	//webColorsLoaded = true;
	//loadPicker();
	getColors(<?= $id ?>);
	fetchColors('blank');
}

function loadPicker() {
	if( curColorsLoaded && webColorsLoaded && !yuiLoaded ) {
		colorPicker = new YAHOO.widget.ColorPicker('ycolorpicker', {
			showhexcontrols: true,
			showwebsafe: false,
			images: {
				PICKER_THUMB: "http://developer.yahoo.com/yui/examples/colorpicker/assets/picker_thumb.png",
				HUE_THUMB: "http://developer.yahoo.com/yui/examples/colorpicker/assets/hue_thumb.png"
			}
		});
		colorPicker.setValue([0,0,0],false);
	
		var picker_drag = new colorPickerDD('yui-picker-swatch');
		var picker_target = new YAHOO.util.DDTarget('school_colors');
		yuiLoaded = true;
	}
}

schoolOptionColorDD = function(id, sGroup, config) {
	schoolOptionColorDD.superclass.constructor.call(this, id, sGroup, config);
};

YAHOO.lang.extend(schoolOptionColorDD, YAHOO.util.DD, {

	obj_id: null,

	onDragOver: function(e, id) {
		if( id == "school_colors" ) {
			//getLayer('school_colors').className = 'highlight';
			currentColorTable.className = 'highlight';
		}
	},

	onDragOut: function(e, id) {
		if( id == "school_colors" ) {
			//getLayer('school_colors').className = '';
			currentColorTable.className = '';
		}
	},

	startDrag: function(x, y) {
		dragged_x = YAHOO.util.Dom.getX(this.obj_id);
		dragged_y = YAHOO.util.Dom.getY(this.obj_id);
	},

	endDrag: function(e) {
		YAHOO.util.Dom.setX(this.obj_id,dragged_x);
		YAHOO.util.Dom.setY(this.obj_id,dragged_y);
	}

});


colorPickerDD = function(id, sGroup, config) {
	this.obj_id = 'yui-picker-swatch';
	colorPickerDD.superclass.constructor.call(this, id, sGroup, config);
};

YAHOO.lang.extend(colorPickerDD, schoolOptionColorDD, {

	onDragDrop: function(e, id) {
		if( id == "school_colors" ) {
			var newColor = getLayer('yui-picker-hex').value;
			ajaxCallback(receivedCurrentColors, '/a/schoolcolors_action.php?color='+newColor+'&sid='+school_id);
			getLayer('school_colors').className = '';
			colorPicker.setValue([0,0,0],false);
		}
	}

});

autoColorDD = function(id, sGroup, config) {
	this.obj_id = id;
	autoColorDD.superclass.constructor.call(this, id, sGroup, config);
};

YAHOO.lang.extend(autoColorDD, schoolOptionColorDD, {

	onDragDrop: function(e, id) {
		if( id == "school_colors" ) {
			var autoColorObj = getLayer(this.obj_id);

			var newColor = autoColorObj.autoColorValue;
			ajaxCallback(receivedCurrentColors, '/a/schoolcolors_action.php?color='+newColor+'&sid='+school_id);
			getLayer('school_colors').className = '';

			// remove the dragged color from the auto-color list
			ac_cell = autoColorObj.parentCell;
			ac_cell.removeChild(autoColorObj);
			ac_cell.innerHTML = '&nbsp;';

			var picker_target = new YAHOO.util.DDTarget('school_colors');
		}
	}

});


Event.onDOMReady(initPage);

</script>

<style type="text/css">

	#ycolorpicker {
		position: relative;
		background-color: #EEEEEE;
		height: 200px;
		width: 330px;
		border: 1px #CCCCCC solid;
	}

	#yui-picker-swatch {
		width: 37px;
		height: 37px;
	}

	.highlight {
		background-color: #FFFF33;
	}


	#auto_colors .content_cell {
		height: 43px;
	}

	#school_website {
		width: 200px;
	}

	#auto_color_form {
		margin-bottom: 4px;
	}
	
	.color_table {
		margin-bottom: 3px;
	}
	
	.school_color_num {
		text-align: center;
	}

</style>

<h2><?= $school['school_name'] ?></h2>
<?php
if( IsAdmin() ) {
	echo '<div style="margin-top: 4px">';
	echo 'Switch Organization: ';
	$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type!="HS" ORDER BY school_name','school_name','id');
	$schools = array("-1"=>'') + $schools_;
	echo GenerateSelectBox($schools,'school_id',-1,'switch_school(this.value)');
	echo '</div>';
}
?>
<hr>


<h3>Current Colors</h3>
	<div id="school_colors" class="color_table"></div>
	<div id="colors_help" class="grey">
		Changes are saved as soon as you see them here.<br>
		If you remove a color currently in use by some drawings, you will have a chance to reassign the objects to a new color.
	</div>
<hr>

<h3>Find Colors</h3>
	<div id="auto_color_form">Website: <input value="<?= $school['school_website'] ?>" type="textbox" id="school_website" name="school_website"> <input type="button" onclick="fetchColors('website')" class="submit" value="Find Colors"></div>
	<div id="auto_colors" class="color_table"></div>
	<div id="auto_color_help" class="grey">Enter a website above and click "Find Colors" to automatically fetch the RGB color codes from the HTML and any related CSS files.</div>

<h3>Color Picker</h3>
	<div id="ycolorpicker"></div>
	<div id="picker_help" class="grey">Select a color, then drag the swatch onto the "Current Colors" list above. Note: If the picker target or sliding bar appear to be misaligned, reload the page to correct this.</div>



<?php

}


?>