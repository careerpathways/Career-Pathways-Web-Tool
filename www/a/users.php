<?php
chdir("..");
include("inc.php");

ModuleInit('users');


/*
 ** Main user management page **

 State admins (127) can edit all users.
 School admins, staff, and webmasters (16,64,96) can only edit/add users from their school, and cannot edit super-admins.
 School admins can delete users at the school

*/


if( KeyInRequest('id') || Request('key') ) {

	if( Request('key') != '' ) {
		$temp = $DB->SingleQuery('SELECT id, approved_by FROM users WHERE application_key="'.Request('key').'"');
		if( is_array($temp) ) {
			$_REQUEST['id'] = $temp['id'];

			if( $temp['approved_by'] != '' ) {
				PrintHeader();
				$appr = $DB->SingleQuery('SELECT * FROM users WHERE id='.$temp['approved_by']);
				echo '<p>This application request has already been approved by '.$appr['first_name'].' '.$appr['last_name'].'</p>';	
				PrintFooter();			
				die();
			}

		} else {
			PrintHeader();
			echo '<p>The application key doesn\'t exist</p>';
			PrintFooter();
			die();
		}
	}


	if( Request('id') != "" ) {
		$check = $DB->SingleQuery("SELECT COUNT(*) AS num FROM users WHERE id=".intval($_REQUEST['id']));
		if( $check['num'] == 0 ) {
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}
	}


	// Staff users can only view, not edit
	if( $_SESSION['user_level'] == CPUSER_STAFF ) {
		if( Request('id') == '' ) {
			header("Location: ".$_SERVER['PHP_SELF']);
		} else {
			PrintHeader();
			ShowExistingUser($DB->SingleQuery("SELECT * FROM users WHERE id=".Request('id')),false);
			PrintFooter();
		}
		die();
	}


	// check permissions on this id.
	if( !IsAdmin() && Request('id') ) {
		$valid = true;

		// check school_id of the requested user
		$school_id = $DB->GetValue('school_id','users', Request('id'));
		if( $school_id != $_SESSION['school_id'] ) {
			$valid = false;
		}

		// check user level of the requested user
		if( $valid ) {
			$user_level = $DB->GetValue('user_level','users', Request('id'));
			if( $user_level > $_SESSION['user_level'] ) {
				$valid = false;
			}
		}

		if( !$valid ) {
			PrintHeader();
			ShowExistingUser($DB->SingleQuery("SELECT * FROM users WHERE id=".Request('id')),false);
			PrintFooter();
			die();
		}
	}



	if( KeyInRequest('chown') ) {
		$from_id = $_REQUEST['id'];
		$to_id = $_REQUEST['to_id'];

		$DB->Query("UPDATE drawing_main SET created_by=$to_id WHERE created_by=$from_id");
		$DB->Query("UPDATE drawing_main SET last_modified_by=$to_id WHERE last_modified_by=$from_id");
		$DB->Query("UPDATE drawings SET created_by=$to_id WHERE created_by=$from_id");
		$DB->Query("UPDATE drawings SET last_modified_by=$to_id WHERE last_modified_by=$from_id");

		header("Location: ".$_SERVER['PHP_SELF']."?id=".$from_id);
		die();
	}




	if( KeyInRequest('delete') ) {
		// if there are any drawings for this user, then mark as inactive, otherwise remove record

		$user_id = $_REQUEST['id'];
		$drawings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawing_main
			WHERE created_by=$user_id OR last_modified_by=$user_id");
		$versions = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawings
			WHERE created_by=$user_id OR last_modified_by=$user_id");

		$has_drawings = $drawings['num'] > 0 || $versions['num'] > 0;

		if( $has_drawings ) {
			$DB->Query("UPDATE users SET user_active=0 WHERE id=".$user_id);
		} else {
			$DB->Query("DELETE FROM users WHERE id=".$user_id);
		}

		header("Location: ".$_SERVER['PHP_SELF']);
		die();
	}

	if( PostRequest() ) {
		if( $_SESSION['user_level'] == CPUSER_STAFF ) {
			$_REQUEST['user_level'] = CPUSER_STAFF;
		}

		$content = array( 'first_name' => $_REQUEST['first_name'],
						  'last_name' => $_REQUEST['last_name'],
						  'job_title' => $_REQUEST['job_title'],
						  'phone_number' => $_REQUEST['phone_number'],
						  'email' => $_REQUEST['email'],
						  'user_level' => intval($_REQUEST['user_level']),
						  );

		if( $_SESSION['user_level'] == CPUSER_STATEADMIN  ) {
			// only state admins can change the school someone is assigned to
			$content['school_id'] = $_REQUEST['school_id'];
		} else {
			// this forces the school_id of the edited user to be the same as the current user's
			$content['school_id'] = $_SESSION['school_id'];

			// prevent users from faking input in an http request
			$content['user_level'] = min($_SESSION['user_level'],$content['user_level']);
		}

		if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
			$login_url = "https://".$SITE->https_server()."/a/login.php";
		} else {
			$login_url = "http://".$_SERVER['SERVER_NAME']."/a/login.php";
		}

		if( Request('id') ) {
		// this is an edit

			switch( $_REQUEST['submit'] ) {
			case 'Deny':
				$content['new_user'] = 0;
				$content['user_active'] = 0;
				$DB->Update('users',$content,$_REQUEST['id']);

				break;
			case 'Approve & Send Password':
				// generate a temporary password and send a welcome email
				
				if( $content['school_id'] == 0 ) {
					PrintHeader();
					echo '<p>Error: Before you can send this user a password, you must assign them to a school. You will need to create the school record first.</p>';
					PrintFooter();
					die();
				} else {

					$password = RandPass(6);
					$content['temp_password'] = crypt($password, $DB->pswdsalt);
					$content['user_active'] = 1;
	
					$email = new SiteEmail('account_approved');
					$email->IsHTML(false);
					$email->Assign('EMAIL', $_REQUEST['email']);
					$email->Assign('PASSWORD', $password);
	
					$email->Assign('LOGIN_LINK', $login_url.'?email='.$_REQUEST['email'].'&password='.$password);
		
					$email->Send();
				
					$content['new_user'] = 0;
					$content['approved_by'] = $_SESSION['user_id'];
					$DB->Update('users',$content,$_REQUEST['id']);
				}
			
				break;
			case 'Save Changes':
				// editing an existing user
				$DB->Update('users',$content,$_REQUEST['id']);

				break;
			case 'Send New Password':
			
				$password = RandPass(6);
				$content['temp_password'] = crypt($password, $DB->pswdsalt);
				$DB->Update('users',$content,$_REQUEST['id']);

			
				$email = new SiteEmail('temporary_password');
				$email->IsHTML(false);
				$email->Assign('LOGIN_LINK', $login_url.'?email='.$content['email'].'&password='.$password);
				$email->Assign('PASSWORD', $password);
				$email->Assign('EMAIL', $content['email']);
				$email->Send();
			
				break;
			}

		} else {
		// this is a new user, check if they already exist in the database

			$check = $DB->MultiQuery("SELECT * FROM users WHERE user_active=1 AND ((email='".$content['email']."') OR (first_name='".$content['first_name']."' AND last_name='".$content['last_name']."'))");
			$user_exists = (count($check) > 0);

			if( !$user_exists ) {
				// add the record, generate a new password and send

				$password = RandPass(6);
				$content['temp_password'] = crypt($password, $DB->pswdsalt);
				$content['user_active'] = 1;
				$content['new_user'] = 0;
				$user_id = $DB->Insert('users',$content);


				$email = new SiteEmail('account_approved');
				$email->IsHTML(false);
				$email->Assign('LOGIN_LINK', $login_url.'?email='.$content['email'].'&password='.$password);
				$email->Assign('PASSWORD', $password);
				$email->Assign('EMAIL', $content['email']);
				$email->Send();

			} else {
				// show details about the existing user

				PrintHeader();
				ShowExistingUser($check[0]);
				PrintFooter();
				die();
			}
		}

		header("Location: ".$_SERVER['PHP_SELF']);
		die();
		
	} else {

		PrintHeader();
		ShowUserForm($_REQUEST['id']);
		PrintFooter();

	}

} else {

	PrintHeader();

	echo '<table>';

	if( IsAdmin() ) {
		$schools = $DB->MultiQuery("
			SELECT schools.*
			FROM schools
			INNER JOIN users ON users.school_id=schools.id
			GROUP BY schools.id
			ORDER BY schools.id=".$_SESSION['school_id']." DESC, school_name
			");
	} else {
		$schools = $DB->MultiQuery("SELECT schools.* 
			FROM schools 
			ORDER BY id=".$_SESSION['school_id']." DESC, school_name
			");
	}

	if( IsWebmaster() ) { 
	echo '<tr>';
		echo '<td colspan="4"><a href="'.$_SERVER['PHP_SELF'].'?id" class="edit"<img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">add user</span></a></td>';
	echo '</tr>';
	}

	// PENDING USERS
	{
		// Only state admins can approve users at other schools
		$users = GetPendingUsers();
		if( count($users) > 0 ) {
	
			echo '<tr><td colspan="6">';
			echo '<div style="font-weight:bold;margin-top:20px;color:#003366;">Users Pending Approval</div>';
			echo '</td></tr>';	
		
			foreach( $users as $u ) {
				echo '<tr>';
	
				if( $u['user_level'] > $_SESSION['user_level'] ) {
					$edit_text = 'view';
	
				} else {
					$edit_text = 'edit';
	
				}
				echo '<td width="30"><a href="'.$_SERVER['PHP_SELF'].'?id='.$u['id'].'" class="edit">'.$edit_text.'</a></td>';
	
				echo '<td width="180">'.$u['first_name'].' '.$u['last_name'].'</td>';
				echo '<td width="140">'.$u['phone_number'].'</td>';
	
				echo '<td width="180">'.$u['email'].'</td>';
				echo '<td width="100" colspan="2">'.$u['school_name'].'</td>';
	
				echo '</tr>';
			}
		}
	}



	foreach( $schools as $i=>$s ) {

		echo '<tr><td colspan="6">';
		echo '<div style="font-weight:bold;margin-top:20px;color:#003366;">'.$s['school_name'].'</div>';
		if( $s['school_addr'] ) { echo '<div style="font-style:italic;margin-left:10px;">'.$s['school_addr'].', '.$s['school_city'].', '.$s['school_state'].' '.$s['school_zip'].'</div>'; }
		echo '</td></tr>';

		$users = $DB->MultiQuery("
			SELECT users.id, first_name, last_name, email, phone_number, lev.name AS user_level_name, user_level, last_logon, last_logon_ip, school_id
			FROM users, admin_user_levels AS lev
			WHERE school_id=".$s['id']."
				AND lev.level = users.user_level
				AND user_active = 1

			");
		if( count($users) == 0 ) {
			echo '<tr>';
			echo '<td>&nbsp;</td>';
			echo '<td colspan="5">none</td>';
			echo '</tr>';
		} else {

			foreach( $users as $u ) {
				echo '<tr>';

				if( !IsGuestUser() ) {
					if( $_SESSION['user_level'] == CPUSER_STAFF || 
						$u['user_level'] > $_SESSION['user_level'] || 
						(!IsAdmin() && $u['school_id'] != $_SESSION['school_id']) ) {
						$edit_text = 'view';
					} else {
						$edit_text = 'edit';
	
					}
					echo '<td width="30"><a href="'.$_SERVER['PHP_SELF'].'?id='.$u['id'].'" class="edit">'.$edit_text.'</a></td>';
				} else {
					echo '<td width="30">&nbsp;</td>';
				}

				echo '<td width="180">'.$u['first_name'].' '.$u['last_name'].'</td>';
				echo '<td width="140">'.(!IsGuestUser()?$u['phone_number']:'&nbsp;').'</td>';

				echo '<td width="180">'.(!IsGuestUser()?$u['email']:'&nbsp;').'</td>';
				echo '<td width="100">'.$u['user_level_name'].'</td>';
				echo '<td width="140"><div title="'.($u['last_logon_ip']==''?'':$u['last_logon_ip']).'">'.($u['last_logon_ip']==''?'':$DB->Date("n/j/Y g:ia",$u['last_logon'])).'</div></td>';

				echo '</tr>';
			}
		}

		if( $i==0 ) { echo '<tr><td colspan="6"><hr></td></tr>'; }
	}

	echo '</table>';

	PrintFooter();

}



function ShowUserForm($id="") {
global $DB;

	$user = $DB->LoadRecord('users',$id);
	if( Request('id') == "" ) {
		// adding a new user. set defaults
		$user['user_active'] = 1;
		$user['school_id'] = $_SESSION['school_id'];
	}

?>
<script type="text/javascript">
	function deleteConfirm() {
		getLayer('delete_confirm').innerHTML = 'Are you sure? <a href="<?= $_SERVER['PHP_SELF'].'?delete&id='.$id ?>">Yes</a> <a href="javascript:deleteCancel()">No</a>';
	}
	function deleteCancel() {
		getLayer('delete_confirm').innerHTML = '';
	}
</script>

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br>
<br>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="userform">
<table width="100%">

	<?php
	if( $user['new_user'] == 1 ) {
	?>
	<tr>
		<td colspan="2" class="noborder"><h2>User Account Request</h2></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
	<tr>
		<td width="100" class="noborder">First Name:</td>
		<td class="noborder"><input type="text" name="first_name" value="<?= $user['first_name'] ?>" size="20"></td>
	</tr>
	<tr>
		<td height="22" class="noborder">Last Name:</td>
		<td class="noborder"><input type="text" name="last_name" value="<?= $user['last_name'] ?>" size="20"></td>
	</tr>
	<tr>
		<td class="noborder">Job Title:</td>
		<td class="noborder"><input type="text" name="job_title" value="<?= $user['job_title'] ?>" size="40"></td>
	</tr>
	<tr>
		<td class="noborder">Phone Number:</td>
		<td class="noborder"><input type="text" name="phone_number" value="<?= $user['phone_number'] ?>" size="20"></td>
	</tr>
	<tr>
		<td class="noborder">Email:</td>
		<td class="noborder"><input type="text" name="email" value="<?= $user['email'] ?>" size="40"></td>
	</tr>
	<tr>
		<td class="noborder">Password:</td>
		<td class="noborder">
		<?php
			if( $user['password'] != "" ) {
				$password_ = "Password is set";
			}
			if( $user['temp_password'] != "" ) {
				$password_ = "Temporary password is set";
			}
			if( $user['temp_password'] == "" && $user['password'] == "" ) {
				$password_ = "None";
			}
			echo $password_;
		?>
		</td>
	</tr>

	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
	<?php
	if( IsAdmin() ) { ?>
		<tr>
			<td class="noborder">Organization:</td>
			<td class="noborder">
			<?php
				if( $user['school_id'] == 0 ) {
					echo 'Other: '.$user['other_school'].'<br>';
					echo '<p>Before this user will be able to log in, you must create a School record for them and assign them to that school. You can alternatively assign them to an existing school.</p>';
					$addl[0] = 'Other (Login Disabled)';
				} else {
					$addl = array();
				}
				echo GenerateSelectBoxDB('schools','school_id','id','school_name','school_name',$user['school_id'],$addl);
			?>
			</td>
		</tr>
	<?php }
	if( $_SESSION['user_level'] > CPUSER_STAFF ) { ?>
	<tr>
		<td class="noborder">User Level:</td>
		<td class="noborder">
		<?php
			// should only be able to create users with equal or less privileges than oneself
			echo GenerateSelectBoxDB('admin_user_levels','user_level','level','name','level',$user['user_level'],array(),"level<=".$_SESSION['user_level']);
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
	<?php
	}
	if( $user['new_user'] == 1 ) {
	?>
	<tr>
		<td>Referred By:</td>
		<td><?= $user['referral'] ?></td>
	</tr>
	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>

	<tr>
		<td class="noborder">&nbsp;</td>
		<td class="noborder">
			<input type="submit" name="submit" value="Approve & Send Password" class="submit">
			<input type="submit" name="submit" value="Deny" class="submit">
		</td>
		<td class="noborder" align="right">&nbsp;</td>
	</tr>

	<?php
	} else {
		?>
	<tr>
		<td>Last Logon:</td>
		<td>
			<?= ($user['last_logon_ip']==''?'':$user['last_logon']) ?>
		</td>
	</tr>
	<tr>
		<td>From:</td>
		<td>
			<?= ($user['last_logon_ip']==''?'':$user['last_logon_ip']) ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>

	<tr>
		<td class="noborder">&nbsp;</td>
		<td class="noborder">
			<?php if( $id == "" ) { ?>
			<input type="submit" name="submit" value="Add & Send Password" class="submit">
			<?php } else { ?>
			<input type="submit" name="submit" value="Save Changes" class="submit">
			<input type="submit" name="submit" value="Send New Password" class="submit">
			<?php } ?>
			</td>
		<td class="noborder" align="right">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php
		if( $id != "" ) {
			ShowVersionsForUser($id);
		}
		?>
		</td>
	</tr>
	<?php
		if( IsWebmaster() ) {
		?>
		<tr>
			<td colspan="2" class="noborder"><hr></td>
		</tr>
		<tr>
			<td valign="top" class="noborder">Delete User:</td>
			<td><span id="delete_link"><a href="javascript:deleteConfirm()">Click to delete</a></span> &nbsp; <span id="delete_confirm"></span><br>
			Note: It is generally not a good idea to delete users if there are drawings associated with them.
			</td>
		</tr>
		<?php
		}
	}
	?>

</table>
<input type="hidden" name="id" value="<?= $id ?>">
</form>
<?php

}

function ShowExistingUser($user,$tried_to_add=true) {
global $DB;

?>
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br>
<br>

<?php
if( $tried_to_add ) {
	echo '<b>An existing record for the person you are trying to add was found in the database. If you are trying to change some information about this user, you should edit the existing record.</b>';
}

?>

<table width="100%">
	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
	<tr>
		<td width="100" class="noborder">First Name:</td>
		<td class="noborder"><?= $user['first_name'] ?></td>
	</tr>
	<tr>
		<td class="noborder">Last Name:</td>
		<td class="noborder"><?= $user['last_name'] ?></td>
	</tr>
	<tr>
		<td class="noborder">Job Title:</td>
		<td class="noborder"><?= $user['job_title'] ?></td>
	</tr>
	<tr>
		<td class="noborder">Phone Number:</td>
		<td class="noborder"><?= $user['phone_number'] ?></td>
	</tr>
	<tr>
		<td class="noborder">Email:</td>
		<td class="noborder"><?= $user['email'] ?></td>
	</tr>
	<?php if( IsWebmaster() ) { ?>
	<tr>
		<td class="noborder">Password:</td>
		<td class="noborder">
		<?php
			if( $user['password'] != "" ) {
				$password_ = "Password is set";
			}
			if( $user['temp_password'] != "" ) {
				$password_ = "Temporary password is set";
			}
			if( $user['temp_password'] == "" && $user['password'] == "" ) {
				$password_ = "None";
			}
			echo $password_;
		?>
		</td>
	</tr>
	<?php } ?>

	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
	<tr>
		<td class="noborder">School:</td>
		<td class="noborder">
		<?= $DB->GetValue('school_name','schools',$user['school_id']) ?>
		</td>
	</tr>
	<tr>
		<td class="noborder">User Level:</td>
		<td class="noborder">
		<?= $DB->GetValue('name','admin_user_levels',$user['user_level'],'level') ?>
		</td>
	</tr>
	<?php if( IsWebmaster() ) { ?>
	<tr>
		<td>Last Logon:</td>
		<td>
			<?= ($user['last_logon_ip']==''?'':$user['last_logon']) ?>
		</td>
	</tr>
	<tr>
		<td>From:</td>
		<td>
			<?= ($user['last_logon_ip']==''?'':$user['last_logon_ip']) ?>
		</td>
	</tr>
	<?php } ?>

	<tr>
		<td colspan="2" class="noborder"><hr></td>
	</tr>
</table>
<?php

	ShowVersionsForUser($user['id']);
}




function ShowVersionsForUser($user_id) {
global $DB;
	?>
	<br>
	<b>Drawings associated with this user:</b><br>
	<?php

	$drawings = GetDrawingsForUser($user_id);
	foreach( $drawings as &$parent ) {
		$versions = $DB->ArrayQuery("
			SELECT *
			FROM drawings
			WHERE drawings.parent_id=".$parent['id']."
				AND deleted=0
				AND (created_by=".$user_id." OR last_modified_by=".$user_id.")
			ORDER BY version_num");
		$parent['drawings'] = $versions;
	}

	if( count($drawings) > 0 ) {
		ShowDrawingList($drawings);
	} else {
		echo '<p>none</p>';
	}

}


function GetDrawingsForUser($user_id) {
global $DB;
	$drawings = $DB->MultiQuery("
		SELECT drawing_main.id, CONCAT(school_abbr,': ',IF(name='','(no title)',name)) AS name, code,
			created_by, last_modified_by, date_created, last_modified, school_id
		FROM drawing_main, schools
		WHERE school_id=schools.id
			AND drawing_main.id IN (SELECT parent_id FROM drawings WHERE (created_by=".$user_id." OR last_modified_by=".$user_id."))
		ORDER BY name");
	return $drawings;
}


?>
