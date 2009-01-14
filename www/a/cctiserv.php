<?php
chdir("..");
include("inc.php");


ModuleInit('ccti_drawings');


// permissions checks

$drawing_id = Request('drawing_id');
//CCTI_check_permission($drawing_id);



switch( Request('action') )
{
case 'save':
	switch( Request('type') )
	{
		case 'data':
			$check = $DB->SingleQuery('SELECT id FROM ccti_data 
				WHERE section_id='.intval(Request('section')).'
					AND row='.intval(Request('row')).'
					AND col='.intval(Request('col')));
			if( is_array($check) ) {
				if( trim(Request('text')) == '' ) {
					$DB->Query('DELETE FROM ccti_data WHERE id='.$check['id']);
				} else {
					$data['text'] = Request('text');
					$DB->Update('ccti_data', $data, $check['id']);
				}
			} else {
				$data['section_id'] = Request('section');
				$data['row'] = Request('row');
				$data['col'] = Request('col');
				$data['text'] = Request('text');
				$DB->Insert('ccti_data', $data);
			}
			break;

		case 'label':
			if( !in_array(Request('extra'), array('x','y','xy')) ) die();

			$check = $DB->SingleQuery('SELECT id FROM ccti_section_labels 
				WHERE section_id='.intval(Request('section')).'
					AND axis="'.Request('extra').'"
					AND row='.intval(Request('row')).'
					AND col='.intval(Request('col')));
			if( is_array($check) ) {
				if( trim(Request('text')) == '' ) {
					$DB->Query('DELETE FROM ccti_section_labels WHERE id='.$check['id']);
				} else {
					$data['text'] = Request('text');
					$DB->Update('ccti_section_labels', $data, $check['id']);
				}
			} else {
				$data['section_id'] = Request('section');
				$data['axis'] = Request('extra');
				$data['row'] = Request('row');
				$data['col'] = Request('col');
				$data['text'] = Request('text');
				$DB->Insert('ccti_section_labels', $data);
			}
			break;

		case 'sectionheader':
			$data['header'] = Request('text');
			$DB->Update('ccti_sections', $data, intval(Request('section')));
			break;

		case 'occ_titles':
		case 'header':
		case 'footer':
		case 'headleft':
		case 'headright':
			$data[Request('type')] = Request('text');
			$DB->Update('ccti_programs', $data, intval(Request('program')));
			break;

	}
	break;
}


function erarr(&$arr) {
	ob_start();
	print_r($arr);
	$str = ob_get_contents();
	ob_end_clean();
	trigger_error($str);
}


?>