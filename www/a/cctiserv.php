<?php
chdir("..");
include("inc.php");


ModuleInit('ccti_drawings');


// permissions checks

$drawing_id = Request('drawing_id');
CCTI_check_permission($drawing_id);


$ccti = new CCTI_Drawing($drawing_id);



switch( Request('action') )
{
	case 'content':
		$ccti->programs[Request('program')]->sections[Request('section')]->content[Request('row')][Request('col')]->text = Request('content');
		$ccti->programs[Request('program')]->sections[Request('section')]->commit();
		break;


}


?>