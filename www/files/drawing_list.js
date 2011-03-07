<?php
header("Content-type: text/javascript");
chdir("..");
include("inc.php");

switch( Request('mode') ) {
	case 'post':
		$module_name = 'post_drawings';
		$session_key = 'post_drawing_list';
		break;
	case 'pathways':
	default:
		$module_name = 'drawings';
		$session_key = 'drawing_list';
		break;
}

ModuleInit($module_name);

// defaults
$school_id = $_SESSION['school_id'];
$people_id = $_SESSION['user_id'];
$categories = "-1";

if( array_key_exists($session_key,$_SESSION) ) {
	$dl = $_SESSION[$session_key];
	if( array_key_exists('school_id',$dl) ) { $school_id = $dl['school_id']; }
	if( array_key_exists('people_id',$dl) ) { $people_id = $dl['people_id']; }
	if( array_key_exists('categories',$dl) ) { $categories = $dl['categories']; }
}

?>

var timeout;
var school_id = "<?= $school_id ?>";
var people_id = "<?= $people_id ?>";
var categories = "<?= $categories ?>";
var loaded_with_search_value = "";

function load_data(selectbox, search) {
	jQuery("#"+selectbox).siblings(".ajaxloader").append('<img src="/images/cf9d2b_loader.gif" />');
	var url = "/a/drawings_load.php?mode="+selectbox+"&"+search+'&type='+MODE;
	ajaxCallback(load_cb, url);
}

function load_cb(data) {
	var obj = eval(data);
	var sel = getLayer(obj[0]);
	jQuery("#"+obj[0]).siblings(".ajaxloader").html('');
	removeAllOptions(sel);
	sel.options[0] = new Option("[Show All]", "-1", false);
	sel.options[0].className = 'even';

	if( MODE == 'post' && sel.id == 'list_schools' )
	{
		sel.options[1] = new Option("[Show All High Schools]", "hs", false);
		sel.options[1].className = 'odd';
		sel.options[2] = new Option("[Show All Community Colleges]", "cc", false);
		sel.options[2].className = 'even';
	}

	for( var i=0; i<obj[1].length; i++ )
	{
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
	ajaxCallback(selectDefaults2, '/a/drawings_load.php?userdefaults&type='+MODE);
	school_id = <?= $_SESSION['school_id'] ?>;
	people_id = <?= $_SESSION['user_id'] ?>;
	categories = "";
}

function selectDefaults2() {
	getLayer('search_box').value = "";
	init();
}

function selectDefaultsGrp() {
	<?php
	$hsids = $DB->VerticalQuery('SELECT hs_id FROM hs_affiliations WHERE cc_id='.$_SESSION['school_id'], 'hs_id');
	$ccids = $DB->VerticalQuery('SELECT cc_id FROM hs_affiliations WHERE hs_id='.$_SESSION['school_id'], 'cc_id');
	$school_ids = array_merge(array($_SESSION['school_id']), $hsids, $ccids);
	echo 'var hss = ['.implode(',', $school_ids).'];'."\n";
	?>
	if( hss.length > 0 )
	{
		var list_schools = getLayer('list_schools');
		for( var i=0; i<list_schools.options.length; i++ ) {
			list_schools.options[i].selected = '';
			for( var j=0; j<hss.length; j++ ) {
				if( list_schools.options[i].value == hss[j] ) {
					list_schools.options[i].selected = "1";
				}
			}
		}
		do_change('list_schools');
	}
}

function init() {
	var url = "/a/drawings_load.php?mode=list_schools&selectdefault&type="+MODE;

	jQuery(".ajaxloader").append('<img src="/images/cf9d2b_loader.gif" />');

	if(getLayer('search_box').value != ""){
		loaded_with_search_value = getLayer('search_box').value;
		do_search();
	}
	
	ajaxCallback(init2, url);
	
	Event.observe(getLayer('search_box'), 'keydown', function(evt) {
	  if (!evt) evt = window.event;
	  if (evt.keyCode == 13) do_search();
	});
}

function init2(data) {
	load_cb(data);
	var url = "/a/drawings_load.php?mode=list_people&school_id="+school_id+"&selectdefault&type="+MODE;
	ajaxCallback(init3, url);
}

function init3(data) {
	load_cb(data);
	var url = "/a/drawings_load.php?mode=list_categories&selectdefault&school_id="+school_id+"&people_id="+people_id+"&type="+MODE;
	ajaxCallback(init4, url);
}

function init4(data) {
	load_cb(data);
	// Load the drawing list from the state of the three boxes only if there was nothing in the search box.
	// If there was a value in the search box, the search has already been performed and the list will update soon.
	if(loaded_with_search_value == ""){
		load_drawing_list();
	}
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

			if(search == ""){
				load_data('list_schools','');
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

			doNoOutput("/a/drawings_load.php?mode=list_categories&categories="+categories_list+"&type="+MODE);

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

function do_reset() {
	getLayer('search_box').value = "";
	do_search();
}

function load_drawing_list() {
	var search_p = get_selected(getLayer('list_people'));
	var search_s = get_selected(getLayer('list_schools'));
	var search_c = get_selected(getLayer('list_categories'));
	var people_list = csl(search_p,',');
	var schools_list = csl(search_s,',');
	var cats_list = csl(search_c,',');
	var search = getLayer('search_box').value;

	var url = "/a/drawings_load.php?mode=drawing_list&search="+search+"&people="+people_list+"&schools="+schools_list+"&categories="+cats_list+"&type="+MODE;
	jQuery("#drawing_list").html('<div style="width:220px; margin:30px auto;"><img src="/images/wide-loader-on-white.gif" /></div>');
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

function preview_drawing(did,vid,mode) {
	if( MODE == 'pathways' )
	{
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/version/'+did+'/'+vid+'.html"></iframe></div>',800,600, null, 'Preview');
	}
	else
	{	
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+did+'/'+vid+'.html"></iframe></div>',800,600, null, 'Preview');
	}
}