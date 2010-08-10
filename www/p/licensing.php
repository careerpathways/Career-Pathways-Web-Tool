<?php
chdir("..");
include("inc.php");

if(array_key_exists('download', $_GET))
{
	$TEMPLATE->AddCrumb('/p/licensing', 'Download Source Code');
	PrintHeader();
	?>
	
	<p>This zip file contains the source code to the Career Pathways Roadmap Web Tool.</p>
	
	<p><a href="source/career-pathways-web-tool.zip">career-pathways-web-tool.zip</a> (5mb)</p>

	<p>You can also browse the source code at <a href="http://cpwebtool.org/trac/browser/trunk">cpwebtool.org</a></p>

	<p>If you would like assistance installing the software, please contact <a href="http://cpwebtool.org/Consulting">Sivecki and Associates, LLC</a> for services and rates.</p>

	<?php
	PrintFooter();
	die();
}

$TEMPLATE->AddCrumb('/p/licensing', 'Licensing');

PrintHeader();
?>

<h3>Download Source Code</h3>
<p style="width: 500px;">The Career Pathways Roadmap Web Tool is available under an open-source license. Please read our 
<a href="license_agreement">license agreement</a>, then <a href="/a/guestlogin.php?download">register</a> to download the source code.</p>

<p>License Agreement (<a href="license_agreement">HTML</a>, <a href="Career Pathways Web Tool License Agreement.pdf" target="_new">PDF</a>)</p>

<a href="/a/guestlogin.php?download">Register</a>

<h3>Installation Requirements</h3>
<ul>
	<li>Fedora Linux server, or a Windows server</li>
	<li>Apache 2.0 with mod_rewrite. It may also be possible to run under a server other than Apache.</li>
	<li>PHP 5.1.6 or later (PHP 5.2 or later recommended)</li>
	<li>MySQL 5.0 or later</li>
	<li>Requires the PHP GD module and the Text_Wiki PEAR module.</li>
	<li>The software requires write permission to several directories. The user running Apache will need write access.</li>
</ul>

<h3>Third-Party Libraries</h3>
<p>The Career Pathways Roadmap Web Tool uses the following open-source libraries, links are provided to their corresponding licenses.</p>
<b>Included</b>
<ul>
	<li><a href="http://prototypejs.org/">Prototype</a> - <a href="http://dev.rubyonrails.org/browser/spinoffs/prototype/trunk/LICENSE">MIT-style license</a></li>
	<li><a href="http://jquery.com/">jQuery</a> - <a href="http://jquery.org/license">MIT license</a></li>
	<li><a href="http://www.texotela.co.uk/code/jquery/select/">jQuery Select</a> - <a href="http://www.opensource.org/licenses/mit-license.php">MIT license</a></li>
	<li><a href="http://code.google.com/p/explorercanvas/">ExCanvas</a> - <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache license</a></li>
	<li><a href="http://jqueryui.com/about">jQuery UI</a> - <a href="http://jqueryui.com/latest/MIT-LICENSE.txt">MIT license</a></li>
	<li><a href="http://keithdevens.com/software/phpxml">XML Library by Keith Evans</a> - <a href="http://keithdevens.com/software/license">Artistic License</a></li>
</ul>
<b>Download Separately</b>
<ul>
	<li><a href="http://phpmailer.worxware.com/">PHPMailer</a> - <a href="http://www.gnu.org/licenses/lgpl.html">LGPL</a></li>
</ul>

<h3>Consulting</h3>
<p>If you would like assistance installing the software, please contact <a href="http://cpwebtool.org/Consulting">Sivecki and Associates, LLC</a> for services and rates.</p>

<h3>Code Modification</h3>
<p>According to <a href="license_agreement#section-3c">section 3 C</a> of the license agreement, you are required to provide a copy of any 
modifications you make back to us. Please send us your changes to <a href="mailto:code@ctepathways.org">code@ctepathways.org</a> or contact
us at this address to arrange other delivery options.</p>


<?php
PrintFooter();
?>