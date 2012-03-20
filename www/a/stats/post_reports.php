<?php
include('stats.inc.php');

PrintHeader();

?>
<style type="text/css">
.section {
  margin-bottom: 40px;
}
.section td, .section th {
  padding: 2px 4px;
}
</style>
<?php


PrintStatsMenu();



$numHSUsers = $DB->SingleQuery('
      SELECT COUNT(1) AS num 
      FROM users u
      JOIN schools s ON u.school_id = s.id
      WHERE organization_type="HS"');
$numHSUsers = $numHSUsers['num'];
$numCCUsers = $DB->SingleQuery('
      SELECT COUNT(1) AS num 
      FROM users u
      JOIN schools s ON u.school_id = s.id
      WHERE organization_type="CC"');
$numCCUsers = $numCCUsers['num'];



echo '<h2>Active Users</h2>';
# Looking for regular activity. Search for all 2011-2012 dates. Active means that they are doing more than just 
# logging on, and that they are actually creating POST Drawings. Drawings that haven't been created or edited 
# since 2009 shouldn't be counted. 

$oldestActiveYear = date('Y') - 2;

echo '<p><i>Active means that they are doing more than just logging on, and that they are actually creating POST Drawings. 
Only drawings created or edited in ' . $oldestActiveYear . ' or after are counted.</i></p>';

echo '<div class="section">';
echo '<h3>How many of the ('.$numHSUsers.') High School users are "actively" creating POST Drawings/Views?</h3>';
# Report a sum total, as well as a list of user and organization names.

$activeHSUsers = getActiveUsers('HS');

echo '<p><b>Total: ' . count($activeHSUsers) . '</b></p>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Name</th>';
  echo '<th>Organization</th>';
  echo '<th>Number</th>';
  echo '<th>Last Activity</th>';
echo '</tr>';
foreach($activeHSUsers as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['name'] . '</a></td>';
    echo '<td>' . $row['school_name'] . '</td>';
    echo '<td>' . $row['num'] . '</td>';
    echo '<td>' . $row['last_activity'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
echo '<h3>How many of the ('.$numCCUsers.') Community College users are "actively" creating POST Drawings/Views?</h3>';
# Report a sum total, as well as a list of user and organization names.

$activeHSUsers = getActiveUsers('CC');

echo '<p><b>Total: ' . count($activeHSUsers) . '</b></p>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Name</th>';
  echo '<th>Organization</th>';
  echo '<th>Number</th>';
  echo '<th>Last Activity</th>';
echo '</tr>';
foreach($activeHSUsers as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['name'] . '</a></td>';
    echo '<td>' . $row['school_name'] . '</td>';
    echo '<td>' . $row['num'] . '</td>';
    echo '<td>' . $row['last_activity'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
echo '<h3>Who are the most active developers for POST Drawings/Views?</h3>';
echo '<p><i>Top 15 Users</i></p>';
# List user and organization names.
$topPOSTUsers = getTopPOSTUsers();

$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Name</th>';
  echo '<th>Organization</th>';
  echo '<th>Number</th>';
  echo '<th>Last Activity</th>';
echo '</tr>';
foreach($topPOSTUsers as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['name'] . '</a></td>';
    echo '<td>' . $row['school_name'] . '</td>';
    echo '<td>' . $row['num'] . '</td>';
    echo '<td>' . $row['last_activity'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';



echo '<br /><br />';
echo '<h2>Development of POST Drawings</h2>';
# search all dates

echo '<div class="section">';
echo '<h3>How many HS sections were created by HS users?</h3>';

$sections = $DB->MultiQuery('
  SELECT dm.id, dm.name AS drawing_name, dm.last_modified,
    u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name, 
    ds.school_name, ds.id AS school_id,
    SUM(d.published) AS published,
    COUNT(vpost_links.id) AS num_views
  FROM post_drawing_main dm
  JOIN post_drawings d ON dm.id = d.parent_id
  JOIN users u ON dm.created_by = u.id
  JOIN schools us ON us.id = u.school_id AND us.organization_type = "HS"
  JOIN schools ds ON ds.id = u.school_id
  LEFT JOIN vpost_links ON vpost_links.post_id = dm.id
  WHERE dm.school_id IN (SELECT id FROM schools WHERE organization_type = "HS")
  GROUP BY dm.id
  ORDER BY dm.last_modified DESC
');

echo '<p>';
  echo '<b>Total: ' . count($sections) . '</b><br />';
  echo '<b>Published: ' . count(array_filter($sections, 'count_published_drawings')) . '</b><br />';
  echo '<b>In a POST View: ' . count(array_filter($sections, 'count_drawings_in_a_view')) . '</b>';
echo '</p>';

$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Drawing</th>';
  echo '<th>Published</th>';
  echo '<th>In Views</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($sections as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $row['id'] . '">' . $row['drawing_name'] . '</a></td>';
    echo '<td>' . ($row['published'] ? 'Yes' : 'No') . '</td>';
    echo '<td>' . $row['num_views'] . '</td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';

echo '<div class="section">';
echo '<h4>Of the published HS portions, how many have been included in POST Views? Which HSs?</h4>';
$sections = $DB->MultiQuery('
  SELECT ds.school_name, dm.name AS drawing_name, dm.id, COUNT(vpost_links.id) AS num_views, ds.id AS school_id
  FROM post_drawing_main dm
  JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
  JOIN users u ON dm.created_by = u.id
  JOIN schools us ON us.id = u.school_id AND us.organization_type = "HS"
  JOIN schools ds ON ds.id = u.school_id
  JOIN vpost_links ON vpost_links.post_id = dm.id
  WHERE dm.school_id IN (SELECT id FROM schools WHERE organization_type = "HS")
  GROUP BY dm.id
  ORDER BY ds.school_name
');
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Drawing</th>';
  echo '<th>In Views</th>';
  echo '<th>Organization</th>';
echo '</tr>';
foreach($sections as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $row['id'] . '">' . $row['drawing_name'] . '</a></td>';
    echo '<td>' . $row['num_views'] . '</td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';

$CCPostDrawingsInAView = $DB->MultiQuery('
SELECT dm.id
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
JOIN schools s ON s.id = dm.school_id
JOIN vpost_links ON vpost_links.post_id = dm.id
WHERE s.organization_type = "CC"
GROUP BY dm.id
');
$CCPostDrawingsInAView = count($CCPostDrawingsInAView);
$CCPostDrawingsPublished = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
JOIN schools s ON s.id = dm.school_id
WHERE s.organization_type = "CC"
');
$CCPostDrawingsPublished = $CCPostDrawingsPublished['num'];

echo '<div class="section">';
echo '<h3>There are ' . $CCPostDrawingsPublished . ' Published CC POST Drawings</h3>';
echo '<p>Included in a POST View: ' . $CCPostDrawingsInAView . '</b><br />';
echo '</p>';
# Provide a breakdown of how many published POST Drawings for each CC (such as Lane Community College, 20)
$publishedCCDrawingsForSchools = $DB->MultiQuery('
SELECT s.school_name, COUNT(dm.id) AS num_drawings
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
JOIN schools s on dm.school_id = s.id AND s.organization_type = "CC"
GROUP BY dm.school_id
ORDER BY num_drawings DESC
');
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>School</th>';
  echo '<th>Published</th>';
echo '</tr>';
foreach($publishedCCDrawingsForSchools as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td>' . $row['num_drawings'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
$num = $DB->SingleQuery('
SELECT 
(SELECT COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
) + (
SELECT COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
) AS num
');
$num = $num['num'];
echo '<h3>' . $num . ' POST Drawings/Views have been created by an ESD (Other org)</h3>';

echo '<div class="section">';
echo '<h4>How many and for which HSs?</h4>';
$drawings = $DB->MultiQuery('
SELECT school_name, school_id, COUNT(1) AS num
FROM (
  SELECT "drawing" AS type, dm.id, ds.school_name, ds.id AS school_id
    FROM post_drawing_main dm
    JOIN users u ON u.id = dm.created_by
    JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
    JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
  UNION
  SELECT "view" AS type, v.id, ds.school_name, ds.id AS school_id
    FROM vpost_views v
    JOIN users u ON u.id = v.created_by
    JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
    JOIN schools ds ON ds.id = v.school_id AND ds.organization_type = "HS"
) tmp
GROUP BY school_id
ORDER BY num DESC
');
echo '<b>Total: ' . count($drawings) . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>School</th>';
    echo '<th>Drawings/Views</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}
echo '</div>';

echo '<div class="section">';
echo '<h4>How many and for which CCs?</h4>';
$drawings = $DB->MultiQuery('
SELECT school_name, school_id, COUNT(1) AS num
FROM (
  SELECT "drawing" AS type, dm.id, ds.school_name, ds.id AS school_id
    FROM post_drawing_main dm
    JOIN users u ON u.id = dm.created_by
    JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
    JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "CC"
  UNION
  SELECT "view" AS type, v.id, ds.school_name, ds.id AS school_id
    FROM vpost_views v
    JOIN users u ON u.id = v.created_by
    JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
    JOIN schools ds ON ds.id = v.school_id AND ds.organization_type = "CC"
) tmp
GROUP BY school_id
ORDER BY num DESC
');
echo '<b>Total: ' . count($drawings) . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>School</th>';
    echo '<th>Drawings/Views</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}
echo '</div>';

echo '<div class="section">';
$esdPOSTViews = $DB->MultiQuery('
  SELECT v.id, v.name, s.id AS school_id, s.school_name, CONCAT(u.first_name, " ", u.last_name) AS user_name, u.id AS user_id
  FROM vpost_views v
  JOIN users u ON u.id = v.created_by
  JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
');
echo '<h4>' . count($esdPOSTViews) . ' POST Views have been created by ESDs</h4>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
echo '</tr>';
foreach($esdPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';

echo '</div>';


echo '<div class="section">';
$emptyPOSTViews = $DB->MultiQuery('
SELECT v.id, v.name, v.last_modified,
	s.id AS school_id, s.school_name,
	u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name
FROM vpost_views v
LEFT JOIN vpost_links l ON l.vid = v.id
JOIN schools s ON v.school_id = s.id
JOIN users u ON v.created_by = u.id
WHERE l.id IS NULL
ORDER BY last_modified DESC
');
echo '<h3>' . count($emptyPOSTViews) . ' POST Views have no POST Drawings attached</h3>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($emptyPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
$testPOSTViews = $DB->MultiQuery('
SELECT v.id, v.name, v.last_modified,
	s.id AS school_id, s.school_name,
	u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name
FROM vpost_views v
JOIN schools s ON v.school_id = s.id
JOIN users u ON v.created_by = u.id
WHERE name LIKE "%test%"
');
echo '<h3>' . count($testPOSTViews) . ' POST Views have "test" in their name</h3>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
  echo '<th>Age</th>';
echo '</tr>';
foreach($testPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
    echo '<td>' . relative_time($row['last_modified']) . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
echo '<h3>How many "full" POST Views exist? This includes both top HS section and lower CC section together.</h3>';
$postViews = $DB->MultiQuery('
SELECT v.id, v.name, v.last_modified,
	s.id AS school_id, s.school_name,
	u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name
FROM vpost_views v
JOIN vpost_links l ON v.id = l.vid
JOIN schools s ON v.school_id = s.id
JOIN users u ON v.created_by = u.id
GROUP BY v.id
ORDER BY v.last_modified DESC
');
$fullPOSTViews = array();
foreach($postViews as $row) {
  $numTypes = $DB->MultiQuery('
    SELECT organization_type, COUNT(1) AS num_types
    FROM vpost_links l
    JOIN post_drawing_main dm ON l.post_id = dm.id
    JOIN schools s ON s.id = dm.school_id
    WHERE vid = ' . $row['id'] . '
    AND organization_type IN ("HS","CC")
    GROUP BY organization_type
  ');
  if(count($numTypes) == 2){  
    // Skip rows that don't have both CC and HS drawings
    $row['num_types'] = count($numTypes);
    $fullPOSTViews[] = $row;
  }
}

echo '<b>Total: ' . count($fullPOSTViews) . '</b>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($fullPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


echo '<div class="section">';
$numEmbedded = $DB->MultiQuery('
SELECT e.id, e.drawing_id
FROM external_links e
WHERE e.`type` = "post"
GROUP BY e.drawing_id
');
echo '<h3>Provide a quick breakdown on who owns each of the ' . count($numEmbedded) . ' embedded POST Views.</h3>';
# such as Lane Community College, 20

$embedded = $DB->MultiQuery('
SELECT s.id AS school_id, s.school_name, COUNT(tmp.id) AS num
FROM 
(SELECT e.id, e.drawing_id
  FROM external_links e
  WHERE e.`type` = "post"
  GROUP BY e.drawing_id
) tmp
JOIN vpost_views v ON v.id = tmp.drawing_id
JOIN schools s ON s.id = v.school_id
GROUP BY s.id
ORDER BY num DESC
');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Organization</th>';
  echo '<th>Number</th>';
echo '</tr>';
foreach($embedded as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td>' . $row['num'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


PrintFooter();




function getActiveUsers($type) {
  global $DB, $oldestActiveYear;
  return $DB->MultiQuery('
  SELECT user_id, CONCAT(u.first_name, " ", u.last_name) AS name, school_name, SUM(num) AS num, MAX(last_activity) AS last_activity
    FROM 
      (SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM post_drawings
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by
      UNION 
      SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM vpost_views
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by
      /*
      UNION 
      SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM drawings
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by */
      ) AS activity
  JOIN users u ON u.id = activity.user_id
  JOIN schools s ON s.id = u.school_id
    AND s.organization_type = "' . $type . '"
  GROUP BY user_id
  ORDER BY last_activity DESC
  ');
}

function getTopPOSTUsers() {
  global $DB, $oldestActiveYear;
  return $DB->MultiQuery('
  SELECT user_id, CONCAT(u.first_name, " ", u.last_name) AS name, school_name, SUM(num) AS num, MAX(last_activity) AS last_activity
    FROM 
      (SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM post_drawings
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by
      UNION 
      SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM vpost_views
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by
      /*
      UNION 
      SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
        FROM drawings
        WHERE last_modified > "' . $oldestActiveYear . '-01-01"
        GROUP BY last_modified_by */
      ) AS activity
  JOIN users u ON u.id = activity.user_id
  JOIN schools s ON s.id = u.school_id
  GROUP BY user_id
  ORDER BY num DESC
  LIMIT 15
  ');
}

function count_published_drawings($item) {
  return $item['published'];
}
function count_drawings_in_a_view($item) {
  return $item['num_views'] > 0;
}

function relative_time($date) {
  $ts = strtotime($date);
  $seconds = time() - $ts;
  $days = floor($seconds / 60 / 60 / 24);
  if($days == 0)
    return 'less than one day';

  $months = floor($days / 30);
  if($months == 0)
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';

  $years = floor($days / 365);
  if($years == 0)
    return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';

  return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';  

  /*
   * Test
  echo '<pre>';
  for($i=strtotime('2009-01-01'); $i<time(); $i+=86400*14) {
    echo date('Y-m-d', $i) . "\t" . relative_time(date('Y-m-d', $i)) . "\n";
  }
  echo '</pre>';
  */  
}
