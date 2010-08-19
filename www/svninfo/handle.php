<?php
chdir("..");
include("inc.php");

$mode = $_SERVER['REDIRECT_QUERY_STRING'];
switch($mode) {
	case '':
		?>
		run <a href="revision">svn info</a><br><br>
		<?php;
		break;
	case 'revision':
		header("Content-type: text/plain");
		echo shell_exec("/usr/bin/svn info /web/ctepathways.org/test/www | egrep 'Revision|URL'");
		break;
	case 'svnupdate':
		header("Content-type: text/plain");
		$txt = shell_exec("/usr/bin/sudo -u aaron /usr/bin/svn update --non-interactive --username=aaron --password=`cat /www/ctpathways.org/aaronsvnpass` /www/ctpathways.org/dev/www");
		echo $txt;

		$body = "The dev server has been updated.\n";
		$body .= "Request originated from: ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")\n\n";
		$body .= $txt;

		$email = new SiteEmail('svn_update');
		$email->IsHTML(false);
		$email->Assign('BODY',$body);
		$email->Send();

		break;
}
?>
