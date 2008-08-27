<?php
chdir("..");
include("inc.php");


$TEMPLATE->AddCrumb('/p/release_info', 'ADA/504 Accessibility');

PrintHeader();
?>
<p>The Web Tool has undergone a series of recent changes that have been aimed in part at ensuring <strong>Accessibility</strong>. Roadmaps are now drawn using Canvas libraries, enabling cross-browser compatibility, better layout control, and printing/PDF capabilities.</p>

<p><em><strong>The primary features that have been added include:</strong></em></p>

<ol>
    <li>Roadmap data available in XML format</li>
    <li>Explicit connections between boxes in maps (versus just graphical lines)</li>

    <li>Text-only version link available for viewing and navigation of roadmaps</li>
    <li>Cross-browser compatibility</li>
    <li>Font sizes easily increased/decreased</li>
    <li>Printing/PDF generation enabled</li>
</ol>

<p>These features directly meet current accessibility requirements and also provide page designers with the means to make their pages accessible for maps created using the current map features. A summary of applicable accessibility features is provided by the <a href="http://www.w3.org/WAI/WCAG20/quickref/" onclick="window.open(this.href, '_blank'); return false;">W3C Web Accessibility Initiative</a>:</p>

<ul>
    <li><em><strong>Text Alternatives</strong></em>: Provide text alternatives for any non-text content so that it can be changed into other forms people need, such as large print, braille, speech, symbols or simpler language</li>
    <li><em><strong>Adaptable</strong></em>: Create content that can be presented in different ways (for example simpler layout ) without losing information or structure</li>
    <li><em><strong>Distinguishable</strong></em>: Make it easier for users to see and hear content including separating foreground from background </li>
    <li><em><strong>Keyboard Accessible</strong></em>: Make all functionality available from a keyboard </li>

    <li><em><strong>Navigable</strong></em>: Provide ways to help users navigate, find content and determine where they are </li>
    <li><em><strong>Readable</strong></em>: Make text content readable and understandable </li>
    <li><em><strong>Predictable</strong></em>: Make Web pages appear and operate in predictable ways </li>
    <li><em><strong>Compatible</strong></em>: Maximize compatibility with current and future user agents, including assistive technologies</li>
</ul>

<p>Read more about maintaining compliance and viewing Career Pathways roadmaps in accessible formats through our <a href="http://iris.lanecc.edu/pathway/index.php/Getting_Started#ADA.2F504_Accessibility_Compliance" onclick="window.open(this.href, '_blank'); return false;">Getting Started Tutorial</a>.</p>

<?php
PrintFooter();
?>