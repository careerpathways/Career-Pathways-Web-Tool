<?php
chdir("..");
include("inc.php");

if(array_key_exists('download', $_GET))
{
	$TEMPLATE->AddCrumb('/p/licensing', 'Download Source Code');
	PrintHeader();
	?>
	<div style="width: 600px;">	
		<p>You can find the source code to the Career Pathways Roadmap Web Tool on Github.</p>
		
		<p><a href="https://github.com/careerpathways/Career-Pathways-Web-Tool">github.com/careerpathways/Career-Pathways-Web-Tool</a></p>
	
		<?php printConsultingInfo()?>
	</div>
	<?php
	PrintFooter();
	die();
}

$TEMPLATE->AddCrumb('/p/licensing', 'Licensing');

PrintHeader();
?>
<div style="width: 600px;">

<h3>About the Career Pathways Roadmap Web Tool Development Project</h3>
<p>The Oregon Department of Community Colleges and Workforce Development (OCCWD) working in partnership with Oregon's 17 community colleges through the 
Oregon Pathways Alliance developed the Career Pathways Roadmap Web Tool to provide visual maps using web technology for students and citizens to learn more 
about education, training, occupations, careers, and the labor market in Oregon.</p>  
<p>We welcome others state agencies, educational institutions, and organizations to download the source code to develop a comparable Web Tool for the 
students and citizens in their state or region.  The Web Tool was developed with funds from the US Department of Labor Employment and Training 
Administration (US DOL ETA) and the Oregon Community College Strategic Reserve Fund.</p>

<h3>Download Source Code</h3>
<p>Please complete the following steps to download the Web Tool Source Code:</p>
<ul>
<li>Read the <a href="license_agreement">Open Source License Agreement</a>.  License Agreement (<a href="license_agreement">HTML</a>, <a href="Career Pathways Web Tool License Agreement.pdf" target="_new">PDF</a>)</li>
<li>Read the Installation Requirements and Third Party Libraries information below.</li>
<li><a href="/a/guestlogin.php?download">Register</a> to be a licensed user.</li>
<li>Download the Source Code</li>
</ul>
   
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
<b>The following third-party libraries are included in the Career Pathways Roadmap Web Tool:</b>
<ul>
	<li><a href="http://prototypejs.org/">Prototype</a> - <a href="http://dev.rubyonrails.org/browser/spinoffs/prototype/trunk/LICENSE">MIT-style license</a></li>
	<li><a href="http://jquery.com/">jQuery</a> - <a href="http://jquery.org/license">MIT license</a></li>
	<li><a href="http://www.texotela.co.uk/code/jquery/select/">jQuery Select</a> - <a href="http://www.opensource.org/licenses/mit-license.php">MIT license</a></li>
	<li><a href="http://code.google.com/p/explorercanvas/">ExCanvas</a> - <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache license</a></li>
	<li><a href="http://jqueryui.com/about">jQuery UI</a> - <a href="http://jqueryui.com/latest/MIT-LICENSE.txt">MIT license</a></li>
	<li><a href="http://keithdevens.com/software/phpxml">XML Library by Keith Evans</a> - <a href="http://keithdevens.com/software/license">Artistic License</a></li>
</ul>
<b>The following third-party library must be downloaded separately:</b>
<ul>
	<li><a href="http://phpmailer.worxware.com/">PHPMailer</a> - <a href="http://www.gnu.org/licenses/lgpl.html">LGPL</a></li>
</ul>
<?php printConsultingInfo()?>

<h3>Oregon Career Pathway Statewide Initiative</h3>

<p>For more information about the Oregon Career Pathway Initiative go to:</p>
<a href="http://www.oregonpathways.org">www.oregonpathways.org</a>
<p>or contact:</p>
<p>
Mimi Maduro<br />
Pathways Initiative Statewide Director<br />
Dept. of Community Colleges & Workforce Development<br />
<a href="mailto:mmaduro@cgcc.cc.or.us">mmaduro@cgcc.cc.or.us</a>
</p>

</div>

<?php
PrintFooter();

function printConsultingInfo()
{
?>
<h3>Consulting</h3>
<p>If you would like assistance installing or customizing the software, please contact <a href="http://cpwebtool.org/Consulting">Sivecki and Associates, LLC</a> for services and rates.</p>

<h3>Troubleshooting</h3>
<p>If you are experiencing any difficulty downloading the source code after registering, please contact us through our <a href="/a/help">Help Desk</a>. We will respond within 24 business hours.</p>

<h3>Code Modification</h3>
<p>According to <a href="license_agreement#section-3c">section 3 C</a> of the license agreement, you are required to provide a copy of any 
modifications you make back to us. Please <a href="https://github.com/careerpathways/Career-Pathways-Web-Tool">fork the code on Github</a> and send a pull request with your changes, or contact
us at <a href="mailto:code@ctepathways.org">code@ctepathways.org</a> to arrange other delivery options.</p>
<?php
}


?>