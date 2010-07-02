<?php
$objects = $DB->MultiQuery("SELECT * FROM objects WHERE drawing_id=".$drawing['id']);

$data = array();

$connections = array();

foreach( $objects as $obj ) {
    $thisobj = unserialize($obj['content']);

    if( is_array($thisobj) )
    {
	   //get the connection list for this object
	   $objConnections = $DB->MultiQuery("SELECT *, LPAD(color,6,\"0\") AS color FROM connections WHERE source_object_id=".$thisobj['id']);

	   foreach($objConnections as $connection) {
	   	$connections[] = $connection;
	   }

	   $program = $DB->SingleQuery("SELECT * FROM object_type JOIN types ON (object_type.type_id = types.id) WHERE object_type.object_id='".$thisobj['id']."' AND types.family ='program'");
	   $thisobj['config']['program'] = $program['type_id'];
	   $data[] = $thisobj;
    }
}
echo 'var chData = ' . (count($data) > 0 ? json_encode($data) : '[]') . ';' . "\n";

$types = $DB->MultiQuery("SELECT * FROM types WHERE family='program' order by id");

echo 'var types = '. ($types ? json_encode($types) : '[]')."; \n";

/*
	// a sample drawing you can use without grabbing it from the DB
	?>
	var chData = [{"type":"arrow","x":"170","y":"170","h":"15","w":"110","config":{"direction":"e","color":"99cc33"},"id":19},{"type":"box","x":"290","y":"110","h":"220","w":"410","config":{"title":"this is the title of the box","content":"this box supports basic wiki syntax!\n\n* bullet\n* another bullet\n* a third\n* a fourth\n\nany idea why i can't use the mouse to select text in here?","color":"#99cc33","content_html":"<p>this box supports basic wiki syntax!<\/p>\n\n<ul>\n    <li>bullet<\/li>\n    <li>another bullet<\/li>\n    <li>a third<\/li>\n    <li>a fourth<\/li>\n<\/ul>\n\n<p>any idea why i can't use the mouse to select text in here?<\/p>\n\n"},"id":20},{"type":"arrow","x":"170","y":"230","h":"15","w":"110","config":{"direction":"e"},"id":21},{"type":"line","x":"320","y":"410","h":"104","w":"0","config":{"direction":"v"},"id":22}];
	<?php
*/

$title = "";
$title_color = "FFFFFF";
$drawing_status = "";

if( is_array($drawing) ) {

	$colors = $DB->VerticalQuery('SELECT id, hex FROM color_schemes WHERE school_id IN ('.$drawing['school_id'].', 0)', 'hex', 'id');
	$colors[] = 'ffffff';
	$colors[] = '333333';
	$title = $drawing['name'];
	$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=".$drawing['school_id']);

	if( $drawing['name'] == '' )
	{
		$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$drawing['program_id']);
		$title = $program['title'];
	}

	$school_options = $DB->LoadRecord('school_options',$drawing['school_id'],'school_id');
	if( !is_array($school_options) ) {
		// load default values
		$school_options = $DB->LoadRecord('school_options','','school_id');
	}

	if( $drawing['published'] ) {
		$drawing_status = '';
	} elseif( $drawing['frozen'] ) {
		$drawing_status = 'outdated';
	} else {
		$drawing_status = 'draft';
	}

} else {
	// we normally won't get here, only if someone changes the $_REQUEST['id'] manually
	$colors = array('9f0b31',
					'99cc33',
					'006699');
}

global $SITE;
?>

var base_url = 'http://<?= $SITE->base_url() ?>';

var chTitleImg = '<div class="title_img" style="height:19px;font-size:0px;overflow:hidden;background-color:#295a76"><img src="'+base_url+'/files/titles/<?= base64_encode($school['school_abbr']).'/'.base64_encode($title) ?>.png" height="19" width="800"></div>';
<?php
if( $drawing['skillset'] ) {
	?>
	var chSkillset = '<div class="title_skillset" style="font-size:8pt;font-weight:bold;"><?=l('skillset name')?>: <?= $drawing['skillset'] ?></div>';
	<?php
} else {
	?>
	var chSkillset = '';
	<?php
}
?>

var chColor = ['<?= implode("','",$colors) ?>'];

var connections = <?= count($connections) > 0 ? json_encode($connections) : '[]' ?>;

var drawing_status = '<?= $drawing_status ?>';

var versionId = <?= $drawing['id'] ?>;
