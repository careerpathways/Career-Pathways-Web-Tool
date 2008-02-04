<?php
header("Content-type: text/javascript");
chdir("..");
include("inc.php");

ModuleInit('drawings');

// defaults
$school_id = $_SESSION['school_id'];
$people_id = $_SESSION['user_id'];
$categories = "-1";

if( array_key_exists('drawing_list',$_SESSION) ) {
	$dl = $_SESSION['drawing_list'];
	if( array_key_exists('school_id',$dl) ) { $school_id = $dl['school_id']; }
	if( array_key_exists('people_id',$dl) ) { $people_id = $dl['people_id']; }
	if( array_key_exists('categories',$dl) ) { $categories = $dl['categories']; }
}

?>

var timeout;
var school_id = "<?= $school_id ?>";
var people_id = "<?= $people_id ?>";
var categories = "<?= $categories ?>";

function load_data(selectbox, search) {
	var url = "/a/drawings_load.php?mode="+selectbox+"&"+search;
	ajaxCallback(load_cb, url);
}

function load_cb(data) {
	var obj = eval(data);
	var sel = getLayer(obj[0]);
	removeAllOptions(sel);
	sel.options[0] = new Option("[Show All]", "-1", false);
	sel.options[0].className = 'even';
	for( var i=0; i<obj[1].length; i++ ) {
		sel.options[sel.options.length] = new Option(obj[2][i], obj[1][i], false, obj[3][i]);
		sel.options[sel.options.length-1].className = (i%2==0?"odd":"even");
	}
}

function removeAllOptions(from) {
	if( !(from != null && from.options != null) ) {
		return;
	}
	for( var i=(from.options.length-1); i>=0; i-- ) {
		from.options[i] = null;
	}
	from.selectedIndex = -1;
}

function selectDefaults() {
	ajaxCallback(selectDefaults2, '/a/drawings_load.php?userdefaults');
	school_id = <?= $_SESSION['school_id'] ?>;
	people_id = <?= $_SESSION['user_id'] ?>;
	categories = "";
}
function selectDefaults2() {
	getLayer('search_box').value = "";
	init();
}

function init() {
	var url = "/a/drawings_load.php?mode=list_schools&selectdefault";
	ajaxCallback(init2, url);
	Event.observe(getLayer('search_box'), 'keydown', function(evt) {
	  if (!evt) evt = window.event;
	  if (evt.keyCode == 13) do_search();
	});
}

function init2(data) {
	load_cb(data);
	var url = "/a/drawings_load.php?mode=list_people&school_id="+school_id+"&selectdefault";
	ajaxCallback(init3, url);
}

function init3(data) {
	load_cb(data);
	var url = "/a/drawings_load.php?mode=list_categories&selectdefault&school_id="+school_id+"&people_id="+people_id;
	ajaxCallback(init4, url);
}

function init4(data) {
	load_cb(data);
	load_drawing_list();
}

function queue_change(sel) {
	clearTimeout(timeout);
	timeout = setTimeout("do_change('"+sel.id+"')",300);
}

function do_change(whichbox) {
	getLayer('search_box').value = "";
	switch( whichbox ) {
		case 'list_schools':
			// update the people and categories list with the selected schools' ids
			var search = get_selected(getLayer('list_schools'));
			var schools_list = csl(search,',');

			load_data('list_people','school_id='+schools_list);

			if( get_selected(getLayer('list_categories')).length == 0 ) {
				load_data('list_categories','school_id='+schools_list);
			}

			// whenever you change schools, the "show all" on the people list should be selected.
			// this will happen after the load_data('list_people') finishes running, but the 
			// load_drawing_list() method needs selectedIndex=0 in order to not filter on old criteria
			getLayer('list_people').selectedIndex = 0;

			break;
		case 'list_people':
			// update the list of categories that this person has contributed to
			if( get_selected(getLayer('list_categories')).length == 0 ) {
				var search_p = get_selected(getLayer('list_people'));
				var search_s = get_selected(getLayer('list_schools'));
				var people_list = csl(search_p,',');
				var schools_list = csl(search_s,',');
				load_data('list_categories','people_id='+people_list+'&school_id='+schools_list);
			}
			break;
		case 'list_categories':
			var search = get_selected(getLayer('list_categories'));
			var categories_list = csl(search,',');

			var schools = get_selected(getLayer('list_schools'));
			var schools_list = csl(schools,',');

			if( get_selected(getLayer('list_people')).length == 0 ) {
				load_data('list_people','schools='+schools+'&categories='+categories_list);
			}

			if( get_selected(getLayer('list_schools')).length == 0 ) {
				load_data('list_schools','categories='+categories_list);
			}

			doNoOutput("/a/drawings_load.php?mode=list_categories&categories="+categories_list);

			break;
	}
	load_drawing_list();
}

function do_search() {
	getLayer('list_people').selectedIndex = -1;
	getLayer('list_schools').selectedIndex = -1;
	getLayer('list_categories').selectedIndex = -1;
	load_drawing_list();
}

function load_drawing_list() {
	var search_p = get_selected(getLayer('list_people'));
	var search_s = get_selected(getLayer('list_schools'));
	var search_c = get_selected(getLayer('list_categories'));
	var people_list = csl(search_p,',');
	var schools_list = csl(search_s,',');
	var cats_list = csl(search_c,',');
	var search = getLayer('search_box').value;

	var url = "/a/drawings_load.php?mode=drawing_list&search="+search+"&people="+people_list+"&schools="+schools_list+"&categories="+cats_list;
	doSomething(getLayer('drawing_list'),url);
}

function get_selected(selbox) {
	// return an array of values that are selected in the given select box
	var ret = Array();
	for( var i=0; i<selbox.options.length; i++ ) {
		if( selbox.options[i].selected ) {
			if( selbox.options[i].value != -1 ) {
				ret.push(selbox.options[i].value);
			}
		}
	}
	return ret;
}

function csl(arr, sep) {
	// return a comma-separated list of values in an array
	if( sep == "" ) sep = ', ';
	var str = "";
	for( var i=0; i<arr.length; i++ ) {
		if( i>0 ) str += sep;
		str += arr[i];
	}
	return str;
}

function preview_drawing(id) {
	chGreybox.create('<div id="dpcontainer"><iframe src="/c/view.php?id='+id+'"></iframe></div>',800,600);
}