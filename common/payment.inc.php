<?php
require_once 'HTML/QuickForm.php';
require_once 'Validate/Finance/CreditCard.php';
require_once 'Validate/US.php';
require_once 'Validate.php';

function __show_creditcardform($amount, $freeze=false) {
global $TEMPLATE, $SITE;

	// Instantiate the HTML_QuickForm object
	$form = new HTML_QuickForm('ccForm');

	if( $freeze ) {
		$cur = $_SESSION['payment'];
		$defaults['cc_name'] = $cur['cc_name'];
		$defaults['cc_number'] = str_repeat('*',12).substr($cur['cc_number'],-4);
		$defaults['cc_type'] = $cur['cc_type'];
		$defaults['cc_exp'] = $cur['cc_exp'];
		$defaults['cc_vcode'] = str_repeat('*',strlen($cur['cc_vcode']));

		$form->setDefaults($defaults);
	} else {
		if( array_key_exists('payment',$_SESSION) && is_array($_SESSION['payment']) ) {
			$defaults = $_SESSION['payment'];
		}
		unset($defaults['cc_number']);
		unset($defaults['cc_vcode']);
		$form->setDefaults($defaults);
	}

	$cc_types = array('Visa'=>'Visa', 'MasterCard'=>'MasterCard');

	//******************************************
	// Add some elements to the form

	if( Request('error') ) {
		formLine($form);

		$enc = urldecode($_REQUEST['error']);

		$dec = "";
		for( $i=0; $i<strlen($enc); $i++ ) {
			$dec .= chr(ord(substr($enc,$i,1))+2);
		}

		$form->addElement('static', '', 'Error:', $dec);
	}

	formLine($form);

	$form->addElement('text', 'cc_name', 'Name on card:', array('size' => 40, 'maxlength' => 255));

	$ccnumprops = array('size'=>20, 'maxlength'=>24);
	if( $SITE->debug ) {
		$ccnumprops['value'] = '5000300020003003';
	}

	$form->addElement('text', 'cc_number', 'Credit Card Number:', $ccnumprops);
	$form->addElement('select', 'cc_type', 'Type:', $cc_types);

	$options = array(
		'language' => 'en',
		'format' => "m/Y",
		'minYear' => date("Y"),
		'maxYear' => date("Y")+10 );
	$form->addElement('date', 'cc_exp', 'Expiration Date:', $options);
	$form->addElement('text', 'cc_vcode', '3-digit Verification Code:', array('size' => 3, 'maxlength' => 3));

	formLine($form);

	$form->addElement('static', '', 'Total to be charged:', $amount);
	if( !$freeze ) $form->addElement('checkbox', 'cc_sig', 'Signature:', '', array('class'=>'checkbox'));
	if( !$freeze ) $form->addElement('textarea', 'agreement', '', array('rows'=>6, 'cols'=>37));

	formLine($form);

	if( !$freeze ) {
		$form->addElement('hidden', 'action', 'payment');
		$form->addElement('submit', null, 'Submit', array('class' => 'submit'));
	}

	formFixWidth($form);

	//******************************************
	// Define filters and validation rules
	$form->setConstants(array('agreement'=>$TEMPLATE->cart_agreement($amount)));

	$trimmed_fields = array('cc_name', 'cc_number', 'cc_vcode');
	foreach( $trimmed_fields as $field ) {
		$form->applyFilter($field, 'trim');
	}

	$form->addRule('cc_name', 'Please enter your name as it appears on your credit card', 'required');
	$form->addRule('cc_type', 'Please select the type of your credit card', 'required');
	$form->addRule('cc_number', 'Please enter your credit card number', 'required');
	$form->addRule('cc_vcode', 'Please enter the 3-digit code found on the back of your card', 'required');
	$form->addRule('cc_vcode', 'Please enter only numeric digits', 'numeric');
	$form->addRule('cc_exp', 'Please select the expiration date of your credit card', 'required');
	$form->addRule('cc_sig', 'You must read the agreement and check the "Signature" box before submitting this form', 'required');

	$form->registerRule('valid_cc_num','callback', 'validate_ccnum');
	$form->addRule(array('cc_number','cc_type'),'The credit card number is invalid', 'valid_cc_num');

	//******************************************
	// Try to validate a form
	if( $freeze ) {
		$form->freeze();
		$form->display();
	} else {
		if( $form->validate() ) {
			$form->freeze();
			$form->process('process_creditcard', false);
		} else {
			// Output the form
			$TEMPLATE->AddCrumb('',"Payment Information");
			PrintHeader();
			$form->display();
			PrintFooter();
		}
	}
}

function process_creditcard($rawvalues) {
global $DB;

	$database_fields = array('cc_name', 'cc_type', 'cc_number', 'cc_exp', 'cc_vcode');

	foreach( $rawvalues as $key=>$value ) {
		if( in_array($key, $database_fields) ) {
			$values[$key] = $value;
		}
	}

	$values['cc_exp'] = $DB->SQLDateOnly(mktime(0,0,0,$rawvalues['cc_exp']['m'],1,$rawvalues['cc_exp']['Y']));

	$_SESSION['payment'] = $values;

	creditcard_processed();
}


function validate_ccnum($data) {
	$number = $data[0];
	$type = $data[1];
	return Validate_Finance_CreditCard::number($number, $type);
}



function __show_billinginfo($freeze=false) {
global $TEMPLATE, $STATES, $DB;

	$db_gifttypes = $DB->MultiQuery("SELECT * FROM gift_types ORDER BY `order`");
	foreach( $db_gifttypes as $g ) {
		$gift_types[$g['id']] = $g['name'];
	}


	// Instantiate the HTML_QuickForm object
	$form = new HTML_QuickForm('billingForm');

	//******************************************
	// Set defaults for the form elements
	if( array_key_exists('billing',$_SESSION) && is_array($_SESSION['billing']) ) {
		$defaults = $_SESSION['billing'];
		$defaults['cust_email2'] = $_SESSION['billing']['cust_email'];
	} else {
		$defaults = array(
			'bill_state' => 'OR',
			'same_shipping_address' => 1,
		);
	}
	$form->setDefaults($defaults);

	//******************************************
	// Add some elements to the form
	formLine($form);
	$form->addElement('text', 'cust_name_first','First Name:', array('size' => 20, 'maxlength' => 255));
	$form->addElement('text', 'cust_name_last', 'Last Name:', array('size' => 20, 'maxlength' => 255));
	$form->addElement('text',   'cust_phone',        'Phone Number:', array('size' => 20, 'maxlength' => 40));
	$form->addElement('text',   'cust_email',        'Email Address:', array('size' => 40, 'maxlength' => 255));
	if( !$freeze ) { $form->addElement('text',   'cust_email2',       'Confirm your Email Address:', array('size' => 40, 'maxlength' => 255)); }

	formLine($form);
	$form->addElement('text',   'bill_street1', 'Address:', array('size' => 40, 'maxlength' => 255));
	$form->addElement('text',   'bill_street2', 'Address (cont.):', array('size' => 40, 'maxlength' => 255));
	$form->addElement('text',   'bill_city',    'City:', array('size' => 20, 'maxlength' => 255));
	$form->addElement('select', 'bill_state',   'State:', $STATES);
	$form->addElement('text',   'bill_zip',     'Zip:', array('size' => 10, 'maxlength' => 10));
	formLine($form);
	if( !$freeze ) $form->addElement('advcheckbox', 'same_shipping_address', 'This is also my<br>shipping address', '', array('class' => 'checkbox'), array(0,1));
	//$form->addElement('select', 'gift_type',   'Choose the type<br>of gift (optional):', $gift_types);
	//formLine($form);

	if( !$freeze ) {
		$form->addElement('hidden', 'action', 'billing');
		$form->addElement('hidden', 'next', (Request('next')?Request('next'):'default'));
		$form->addElement('submit', null, 'Continue', array('class' => 'submit'));
	}

	formFixWidth($form);

	//******************************************
	// Define filters and validation rules
	$trimmed_fields = array('cust_name_first', 'cust_name_last',
		'bill_street1', 'bill_street2', 'bill_city', 'bill_state', 'bill_zip' );
	foreach( $trimmed_fields as $field ) {
		$form->applyFilter($field, 'trim');
	}

	$form->addRule('cust_name_first', 'Please enter your first name', 'required');
	$form->addRule('cust_name_last', 'Please enter your last name', 'required');
	$form->addRule('cust_phone', 'Please enter your phone number', 'required');
	$form->addRule('bill_street1', 'Please enter your address', 'required');
	$form->addRule('bill_city', 'Please enter your city', 'required');
	$form->addRule('bill_state', 'Please enter your state', 'required');
	$form->addRule('bill_zip', 'Please enter your zip code', 'required');
	$form->addRule('cust_email', 'Please enter your email address', 'required');
	$form->addRule('cust_email2', 'Please confirm your email address', 'required');
	$form->addRule(array('cust_email', 'cust_email2'), 'The email addresses you entered do not match', 'compare');

	$form->registerRule('check_email','callback', 'email', 'Validate');
	$form->addRule('email','Your email address appears to be invalid', 'check_email');

	$form->registerRule('check_zip','callback', 'postalCode', 'Validate_US');
	$form->addRule('bill_zip','Your zip code appears to be invalid', 'check_zip');

	if( $freeze ) {
		$form->freeze();
		$form->display();
	} else {
		// Try to validate a form
		if( $form->validate() ) {
			$form->freeze();
			$form->process('process_billinginfo', false);
		} else {
			// Output the form
			$TEMPLATE->AddCrumb('',"Billing Address");
			PrintHeader();
			$form->display();
			PrintFooter();
		}
	}
}


function process_billinginfo($rawvalues) {

	$database_fields = array(
		'cust_name_first', 'cust_name_last', 'cust_email', 'cust_phone', 'same_shipping_address',
		'bill_street1', 'bill_street2', 'bill_city', 'bill_state', 'bill_zip', 'gift_type' );

	foreach( $rawvalues as $key=>$value ) {
		if( in_array($key, $database_fields) ) {
			$values[$key] = $value;
		}
	}

	$_SESSION['billing'] = $values;

	billinginfo_processed();
}



function __show_shippinginfo($freeze=false) {
global $TEMPLATE, $STATES, $DB;

	// Instantiate the HTML_QuickForm object
	$form = new HTML_QuickForm('shippingForm');

	//******************************************
	// Set defaults for the form elements
	if( array_key_exists('shipping',$_SESSION) && is_array($_SESSION['shipping']) ) {
		$defaults = $_SESSION['shipping'];
	} else {
		$defaults = array(
			'ship_state' => 'OR',
		);
	}
	$form->setDefaults($defaults);

	//******************************************
	// Add some elements to the form
	formLine($form);
	if( array_key_exists('gift_type',$_SESSION['billing']) && $_SESSION['billing']['gift_type'] != "NA" ) {
		$gift = strtolower($DB->GetValue('name','gift_types',$_SESSION['billing']['gift_type']));
		$form->addElement('html', '<tr><td>&nbsp;</td><td>This is '.add_indefinite_article($gift).', and will be<br>shipped directly to the address below.</td></tr>');
		formLine($form);
	}
	$form->addElement('text', 'ship_name_first','First Name:', array('size' => 20, 'maxlength' => 255));
	$form->addElement('text', 'ship_name_last', 'Last Name:', array('size' => 20, 'maxlength' => 255));

	$form->addElement('text',   'ship_street1', 'Address:', array('size' => 40, 'maxlength' => 255));
	$form->addElement('text',   'ship_street2', 'Address (cont.):', array('size' => 40, 'maxlength' => 255));
	$form->addElement('text',   'ship_city',    'City:', array('size' => 20, 'maxlength' => 255));
	$form->addElement('select', 'ship_state',   'State:', $STATES);
	$form->addElement('text',   'ship_zip',     'Zip:', array('size' => 10, 'maxlength' => 10));
	formLine($form);

	if( array_key_exists('gift_type',$_SESSION['billing']) && $_SESSION['billing']['gift_type'] != "NA" ) {
		$form->addElement('textarea', 'card_info', 'Include this note<br>on a card shipped<br>with the order:', array('rows'=>6, 'cols'=>37));
		formLine($form);
	}

	if( !$freeze ) {
		$form->addElement('hidden', 'action', 'shipping');
		$form->addElement('hidden', 'next', (Request('next')?Request('next'):'default'));
		$form->addElement('submit', null, 'Continue', array('class' => 'submit'));
	}

	formFixWidth($form);

	//******************************************
	// Define filters and validation rules
	$trimmed_fields = array('ship_name_first', 'ship_name_last', 'ship_street1', 'ship_street2', 'ship_city', 'ship_state', 'ship_zip' );
	foreach( $trimmed_fields as $field ) {
		$form->applyFilter($field, 'trim');
	}

	$form->addRule('ship_name_first', 'Please enter the name to ship to', 'required');
	$form->addRule('ship_name_last', 'Please enter the name to ship to', 'required');
	$form->addRule('ship_street1', 'Please enter your address', 'required');
	$form->addRule('ship_city', 'Please enter your city', 'required');
	$form->addRule('ship_state', 'Please enter your state', 'required');
	$form->addRule('ship_zip', 'Please enter your zip code', 'required');

	$form->registerRule('check_zip','callback', 'postalCode', 'Validate_US');
	$form->addRule('ship_zip','Your zip code appears to be invalid', 'check_zip');

	// Try to validate a form
	if( $freeze ) {
		$form->freeze();
		$form->display();
	} else {
		if( $form->validate() ) {
			$form->freeze();
			$form->process('process_shippinginfo', false);
		} else {
			// Output the form
			$TEMPLATE->AddCrumb('',"Shipping Address");
			PrintHeader();
			$form->display();
			PrintFooter();
		}
	}
}



function process_shippinginfo($rawvalues) {

	$database_fields = array(
		'ship_name_first', 'ship_name_last', 'ship_street1', 'ship_street2', 'ship_city', 'ship_state', 'ship_zip', 'card_info' );

	foreach( $rawvalues as $key=>$value ) {
		if( in_array($key, $database_fields) ) {
			$values[$key] = $value;
		}
	}

	$_SESSION['shipping'] = $values;

	shippinginfo_processed();
}




function formLine(&$form) {
	$form->addElement('html', '<tr><td colspan="2"><hr></td></tr>');
}

function formFixWidth(&$form) {
global $TEMPLATE;
	$form->addElement('html', '<tr><td colspan="2" width="'.$TEMPLATE->content_width.'" height="1"><img src="/images/clear.gif" height="1" width="1"></td></tr>');
	//$form->addElement('html', '<tr><td width="280" height="1"><img src="/images/clear.gif" height="1" width="1"></td><td><img src="/images/clear.gif" height="1" width="1"></td></tr>');
}

?>