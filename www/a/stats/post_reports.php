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

$org_type_list = $DB->VerticalQuery(
  "SELECT schools.*
  FROM schools
  INNER JOIN users ON users.school_id=schools.id
  GROUP BY schools.id
  ORDER BY school_name", 
  'organization_type',
  'school_name'
);

$numHSUsers = $DB->SingleQuery('
  SELECT COUNT(1) AS num
  FROM users u
  JOIN schools s ON u.school_id = s.id
  WHERE organization_type="HS"
');

$numHSUsers = $numHSUsers['num'];

$numCCUsers = $DB->SingleQuery('
  SELECT COUNT(1) AS num
  FROM users u
  JOIN schools s ON u.school_id = s.id
  WHERE organization_type="CC"
');

$numCCUsers = $numCCUsers['num'];



echo '<h2>POST Reports - Active Users</h2>';
# Looking for regular activity. Searches for current year, minus 2 calendar years. Active means that they are doing more
# than just logging on, and that they are actually creating POST Drawings. Drawings that haven't been created or edited
# since 2009 shouldn't be counted.

$oldestActiveYear = date('Y') - 2;

echo '<p><i>Active means that they are doing more than just logging on, and that they are actually creating POST Drawings. Only drawings created or edited in ' . $oldestActiveYear . ' or after are counted.</i></p>';

# Active High School Users
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

# Active Community College Users
echo '<div class="section">';
echo '<h3>How many of the ('.$numCCUsers.') Community College users are "actively" creating POST Drawings/Views?</h3>';
# Report a sum total, as well as a list of user and organization names.

$activeCCUsers = getActiveUsers('CC');

echo '<p><b>Total: ' . count($activeCCUsers) . '</b></p>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Name</th>';
  echo '<th>Organization</th>';
  echo '<th>Number</th>';
  echo '<th>Last Activity</th>';
echo '</tr>';
foreach($activeCCUsers as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['name'] . '</a></td>';
    echo '<td>' . $row['school_name'] . '</td>';
    echo '<td>' . $row['num'] . '</td>';
    echo '<td>' . $row['last_activity'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


# Top Developers of POST Drawings/Views
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


# Development of POST Drawings
echo '<br /><br />';
echo '<h2>Development of POST Drawings</h2>';
# search all dates


# High School POST Drawing sections created by High School Users
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
echo '</p>';

$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Drawing</th>';
  echo '<th>Published</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($sections as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $row['id'] . '">' . $row['drawing_name'] . '</a></td>';
    echo '<td>' . ($row['published'] ? 'Yes' : 'No') . '</td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';


# High School POST Drawing sections in POST Views
echo '<div class="section">';
echo '<h4>Of the published HS sections, how many have been included in a POST View? For which HSs?</h4>';
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

$HSPostDrawingsInAView = $DB->MultiQuery('
SELECT dm.id, dm.name AS drawing_name, dm.last_modified, ds.school_name, ds.id AS school_id,
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
   echo '<b>In a POST View: ' . count(array_filter($HSPostDrawingsInAView, 'count_drawings_in_a_view')) . '</b>';
echo '</p>';

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


# HS POST Drawings created by Community College Users
echo '<div class="section">';

echo '<table width="100%"><tr>';
echo '<td width="50%" valign="top">';
echo '<h4>POST Drawings Created for HSs by a Community College</h4>';

$sections = $DB->MultiQuery('
SELECT dm.id, dm.name AS drawing_name, dm.last_modified, ds.school_name, ds.id AS school_id, us.id AS org_id, us.school_name AS org_name, 
SUM(d.published) AS published,
COUNT(1) AS num_views
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id
JOIN users u ON u.id = dm.created_by
JOIN schools us ON us.id = u.school_id AND us.organization_type = "CC"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
WHERE dm.school_id IN (SELECT id FROM schools WHERE organization_type = "HS")
GROUP BY dm.id
ORDER BY dm.last_modified DESC
');

echo '<p>';
  echo '<b>Total: ' . count($sections) . '</b><br />';
  echo '<b>Published: ' . count(array_filter($sections, 'count_published_drawings')) . '</b><br />';
echo '</p>';

$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Organization</th>';
  echo '<th>Drawing</th>';
  echo '<th>Published</th>';
  echo '<th>Created By</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($sections as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $row['id'] . '">' . $row['drawing_name'] . '</a></td>';
    echo '<td>' . ($row['published'] ? 'Yes' : 'No') . '</td>';
    echo '<td><a href="/a/schools.php?id=' . $row['org_id'] . '">' . $row['org_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';

# High School POST Drawing sections in POST Views created by CC's
echo '<div class="section">';
echo '<h4>Of the CC created published HS sections, how many have been included in a POST View? For which HSs?</h4>';

$sections = $DB->MultiQuery('
SELECT dm.id, dm.name AS drawing_name, dm.last_modified, ds.school_name, ds.id AS school_id, us.id AS org_id, us.school_name AS org_name, 
COUNT(vpost_links.id) AS num_views, ds.id AS school_id
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
JOIN users u ON dm.created_by = u.id
JOIN schools us ON us.id = u.school_id AND us.organization_type = "CC"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
JOIN vpost_links ON vpost_links.post_id = dm.id
WHERE dm.school_id IN (SELECT id FROM schools WHERE organization_type = "HS")
GROUP BY dm.id
ORDER BY ds.school_name
');

$HSPostDrawingsInAView = $DB->MultiQuery('
SELECT dm.id, dm.name AS drawing_name, dm.last_modified, ds.school_name, ds.id AS school_id,
SUM(d.published) AS published,
COUNT(vpost_links.id) AS num_views
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id
JOIN users u ON dm.created_by = u.id
JOIN schools us ON us.id = u.school_id AND us.organization_type = "CC"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
LEFT JOIN vpost_links ON vpost_links.post_id = dm.id
WHERE dm.school_id IN (SELECT id FROM schools WHERE organization_type = "HS")
GROUP BY dm.id
ORDER BY dm.last_modified DESC
');

echo '<p>';
   echo '<b>In a POST View: ' . count(array_filter($HSPostDrawingsInAView, 'count_drawings_in_a_view')) . '</b>';
echo '</p>';

$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Organization</th>';
  echo '<th>Drawing</th>';
  echo '<th>In Views</th>';
  echo '<th>Created By</th>';
echo '</tr>';
foreach($sections as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $row['id'] . '">' . $row['drawing_name'] . '</a></td>';
    echo '<td>' . $row['num_views'] . '</td>';
    echo '<td><a href="/a/schools.php?id=' . $row['org_id'] . '">' . $row['org_name'] . '</a></td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';

# CC POST Drawings

$CCPostDrawingsInAView = $DB->MultiQuery('
SELECT dm.id
FROM post_drawing_main dm
JOIN post_drawings d ON dm.id = d.parent_id AND d.published = 1
JOIN schools s ON s.id = dm.school_id
JOIN vpost_links ON vpost_links.post_id = dm.id
JOIN vpost_views ON vpost_links.vid = vpost_views.id
WHERE s.organization_type = "CC"
GROUP BY vpost_views.id
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

# Summary of published CC POST Drawings
echo '<div class="section">';
echo '<h3>There are ' . $CCPostDrawingsPublished . ' Published CC POST Drawings</h3>';
echo '<p>POST Views with a published CC drawing: ' . $CCPostDrawingsInAView . '</b><br />';
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
  echo '<th>Organization</th>';
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

# Breakdown of POST Drawings and POST Views created by an ESD
echo '<div class="section">';

$num = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
');
$num_post_drawings = $num['num'];

$num = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
');
$num_post_views = $num['num'];
echo '<h3>' . $num_post_drawings . ' POST Drawings and ' . $num_post_views . ' POST Views have been created by an ESD (Other org)</h3>';

# HS POST Drawings created by an ESD
echo '<div class="section">';

echo '<table width="100%"><tr>';
echo '<td width="50%" valign="top">';
echo '<h4>POST Drawings Created for HSs</h4>';

$drawings = $DB->MultiQuery('
SELECT "drawing" AS type, dm.id, ds.school_name, ds.id AS school_id, COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
GROUP BY school_id
ORDER BY num DESC
');

$numHSdwgs = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "HS"
');
$num_post_HSdwgs = $numHSdwgs['num'];

echo '<b>Total High Schools: ' . count($drawings) . '</b><br />';
echo '<b>Total Drawings: ' . $num_post_HSdwgs . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>Organization</th>';
    echo '<th>Drawings</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}

echo '</td>';

# HS POST Views created by an ESD
echo '<td width="50%" valign="top">';
echo '<h4>POST Views Created for HSs</h4>';

$drawings = $DB->MultiQuery('
SELECT "view" AS type, v.id, ds.school_name, ds.id AS school_id, COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
JOIN schools ds ON ds.id = v.school_id AND ds.organization_type = "HS"
GROUP BY school_id
ORDER BY num DESC
');

$numHSviews = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
JOIN schools ds ON ds.id = v.school_id AND ds.organization_type = "HS"
');
$num_post_HSviews = $numHSviews['num'];

echo '<b>Total High Schools: ' . count($drawings) . '</b><br />';
echo '<b>Total Views: ' . $num_post_HSviews . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>Organization</th>';
    echo '<th>Views</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}

echo '</tr></table>';
echo '</div>';


# CC POST Drawings created by an ESD
echo '<div class="section">';
echo '<table width="100%"><tr>';
echo '<td width="50%" valign="top">';
echo '<h4>POST Drawings Created for CCs or Others</h4>';

$drawings = $DB->MultiQuery('
SELECT "drawing" AS type, dm.id, ds.school_name, ds.id AS school_id, COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type IN ("CC","Other")
GROUP BY school_id
ORDER BY num DESC
');

$numCCdwgs = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM post_drawing_main dm
JOIN users u ON u.id = dm.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
JOIN schools ds ON ds.id = dm.school_id AND ds.organization_type = "CC"
');
$num_post_CCdwgs = $numCCdwgs['num'];

echo '<b>Total Organizations: ' . count($drawings) . '</b><br />';
echo '<b>Total Drawings: ' . $num_post_CCdwgs . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>Organization</th>';
    echo '<th>Drawings</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}

echo '</td>';

# CC POST Views created by an ESD
echo '<td width="50%" valign="top">';
echo '<h4>POST Views Created for CCs or Others</h4>';

$drawings = $DB->MultiQuery('
SELECT "view" AS type, v.id, ds.school_name, ds.id AS school_id, COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools us ON us.id = u.school_id AND us.organization_type = "Other"
JOIN schools ds ON ds.id = v.school_id AND ds.organization_type IN ("CC", "Other")
GROUP BY school_id
ORDER BY num DESC
');

$numCCviews = $DB->SingleQuery('
SELECT COUNT(1) AS num
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
JOIN schools ds ON ds.id = v.school_id AND ds.organization_type = "CC"
');
$num_post_CCviews = $numCCviews['num'];

echo '<b>Total Organizations: ' . count($drawings) . '</b><br />';
echo '<b>Total Views: ' . $num_post_CCviews . '</b>';
if(count($drawings) > 0) {
  $trClass = new Cycler('row_light', 'row_dark');
  echo '<table>';
  echo '<tr class="drawing_main">';
    echo '<th>Organization</th>';
    echo '<th>Views</th>';
  echo '</tr>';
  foreach($drawings as $row) {
    echo '<tr class="' . $trClass . '">';
      echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
      echo '<td>' . $row['num'] . '</td>';
    echo '</tr>';
  }
  echo '</table>';
}

echo '</tr></table>';
echo '</div>';

# Total POST Views created by an ESD
echo '<div class="section">';
$esdPOSTViews = $DB->MultiQuery('
SELECT v.id, v.name, vs.id AS school_id, vs.school_name, CONCAT(u.first_name, " ", u.last_name) AS user_name, u.id AS user_id
FROM vpost_views v
JOIN users u ON u.id = v.created_by
JOIN schools s ON s.id = u.school_id AND s.organization_type = "Other"
JOIN schools vs ON vs.id = v.school_id
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

# POST VIews that have NO POST Drawings attached
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


# POST Views that have "test" in their name
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







# post views that have high school and CC in them.
echo '<div class="section">';
echo '<h3>How many "full" POST Views exist? This includes both top HS section and lower CC section together.</h3>';
$postViews = $DB->MultiQuery('
SELECT v.id, v.name, v.last_modified,
s.id AS school_id, s.school_name,
u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name
FROM vpost_views v
LEFT JOIN vpost_links l ON v.id = l.vid
LEFT JOIN schools s ON v.school_id = s.id
LEFT JOIN users u ON v.created_by = u.id
GROUP BY v.id
ORDER BY v.last_modified DESC
');

$totalPostViews = $postViews; //used in summary report (below)

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


if($SITE->hasFeature('approved_program_name')){

$postViews = $DB->MultiQuery('
    SELECT v.id as view_id, v.name, v.last_modified,
          s.id AS school_id, s.school_name,
          u.id AS user_id, CONCAT(u.first_name, " ", u.last_name) AS user_name,
          pdm.type as type
FROM vpost_views v
  LEFT JOIN vpost_links l
    ON v.id = l.vid
  LEFT JOIN post_drawing_main pdm
    ON pdm.id = l.post_id
  LEFT JOIN schools s
    ON v.school_id = s.id
  LEFT JOIN users u
    ON v.created_by = u.id
  WHERE pdm.type IN ("HS","CC")
  ORDER BY v.last_modified DESC
');


echo '<div class="section">';
echo '<h3>How many POST Views exist that only include top High School Sections?</h3>';

//build exclusion list
$exclusionList = array();
foreach($postViews as $row) {
  if($row['type'] !== 'HS'){
    $exclusionList[$row['view_id']] = 1;
  }
}
$HSonlyPOSTViews = array();
foreach($postViews as $row) {
  if(!array_key_exists($row['view_id'], $exclusionList)){
    if($row['view_id'] > 0){
      $HSonlyPOSTViews[$row['view_id']] = $row;
    }
  }
}

$doc = array();

echo '<b>Total: ' . count($HSonlyPOSTViews) . '</b>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($HSonlyPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['view_id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';



echo '<div class="section">';
echo '<h3>How many POST Views exist that only include bottom Community College Sections?</h3>';

//build exclusion list
$exclusionList = array();
foreach($postViews as $row) {
  if($row['type'] !== 'CC'){
    $exclusionList[$row['view_id']] = 1;
  }
}
$CConlyPOSTViews = array();
foreach($postViews as $row) {
  if(!array_key_exists($row['view_id'], $exclusionList)){
    if($row['view_id'] > 0){
      $CConlyPOSTViews[$row['view_id']] = $row;
    }
  }
}

$doc = array();

echo '<b>Total: ' . count($CConlyPOSTViews) . '</b>';
$trClass = new Cycler('row_light', 'row_dark');
echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>View</th>';
  echo '<th>Organization</th>';
  echo '<th>User</th>';
  echo '<th>Last Modified</th>';
echo '</tr>';
foreach($CConlyPOSTViews as $row) {
  echo '<tr class="' . $trClass . '">';
    echo '<td><a href="/a/post_views.php?id=' . $row['view_id'] . '">' . ($row['name'] ? $row['name'] : '(No Name)') . '</a></td>';
    echo '<td><a href="/a/schools.php?id=' . $row['school_id'] . '">' . $row['school_name'] . '</a></td>';
    echo '<td><a href="/a/users.php?id=' . $row['user_id'] . '">' . $row['user_name'] . '</a></td>';
    echo '<td>' . $row['last_modified'] . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '</div>';




$view_drawings = $DB->MultiQuery('
SELECT  v.id                AS view_id, 
        v.name              AS view_name,
        v.last_modified     AS view_last_modified,
        sv.id               AS view_school_id,
        sv.school_name      AS view_school_name,
        sv.school_zip       AS view_school_zip,
        pdm.name            AS drawing_name,
        pdm.program_id      AS drawing_program_id,
        pdm.type            AS drawing_type,
        pdm.id              AS drawing_id,
        pdm.skillset_id     AS drawing_skillset_id,
        skillsets.title     AS drawing_skillset_title,
        sd.id               AS drawing_school_id,
        sd.school_name      AS drawing_school_name,
        sd.school_zip       AS drawing_school_zip
    FROM vpost_views v
      LEFT JOIN vpost_links l
        ON v.id = l.vid
      LEFT JOIN post_drawing_main pdm
        ON pdm.id = l.post_id
      LEFT JOIN schools sv
        ON v.school_id = sv.id
      LEFT JOIN schools sd
        ON pdm.school_id = sd.id
      LEFT JOIN oregon_skillsets skillsets
        ON pdm.skillset_id = skillsets.id
    WHERE pdm.type IN ("HS","CC")
    ORDER BY v.name ASC
  ');

$inclusion_list = array();

  foreach($view_drawings as $view_drawing){
    $key = $view_drawing['view_school_name'];
    $view_id = $view_drawing['view_id'];

    if(!isset($inclusion_list[$key])){
      $inclusion_list[$key] = array(
        'views' => array()
      );
    }

    if(!isset($inclusion_list[$key]['views'][$view_id])){
      $inclusion_list[$key]['views'][$view_id] = array(
        'hs_drawing_ids' => array(),
        'cc_drawing_ids' => array(),
      );
    }


    if( $view_drawing['drawing_type'] === 'HS' ){
      $inclusion_list[$key]['views'][$view_id]['has_hs'] = true;
      $inclusion_list[$key]['views'][$view_id]['hs_drawing_ids'][] = $view_drawing['drawing_id'];

    }
    if( $view_drawing['drawing_type'] === 'CC' ){
      $inclusion_list[$key]['views'][$view_id]['has_cc'] = true;
      $inclusion_list[$key]['views'][$view_id]['cc_drawing_ids'][] = $view_drawing['drawing_id'];
    }
  }

ksort($inclusion_list);

//subtotal for all community colleges
$cc_subtotal_views = 0;
$cc_subtotal_complete = 0;
$cc_subtotal_hs_only = 0;
$cc_subtotal_cc_only = 0;

//subtotal for all high schools
$hs_subtotal_views = 0;
$hs_subtotal_complete = 0;
$hs_subtotal_hs_only = 0;
$hs_subtotal_cc_only = 0;

//subtotal for all "other orgs"
$other_org_subtotal_views = 0;
$other_org_subtotal_complete = 0;
$other_org_subtotal_hs_only = 0;
$other_org_subtotal_cc_only = 0;

//totals for all schools
$total_views = 0;
$total_complete = 0;
$total_hs_only = 0;
$total_cc_only = 0;

echo '<div class="section">';
  echo '<h3>Provide a quick breakdown on the types of POST Views:</h3>';
  echo '<table>';
    echo '<tr class="drawing_main">';
      echo '<th>Organization</th>';
      echo '<th>Total Embedded<br />POST Views</th>';
      echo '<th>Full POST Views</th>';
      echo '<th>TOP (HS)<br />POST Views Only</th>';
      echo '<th>BOTTOM (CC)<br />POST Views Only</th>';
    echo '</tr>';

    $trClass = new Cycler('row_light', 'row_dark');
    foreach($inclusion_list as $school_name => $viewDoc){
      $number_complete = 0;
      $number_hs_only = 0;
      $number_cc_only = 0;
      foreach($viewDoc['views'] as $view){
        if(isset($view['has_hs']) && isset($view['has_cc'])){
          $number_complete++;
        } elseif (isset($view['has_hs']) && !isset($view['has_cc'])){
          $number_hs_only++;
        } else {
          $number_cc_only++;
        }
      }

      echo '<tr class="' . $trClass . '">';
        echo '<td>' . $school_name . '</td>';
        echo '<td>' . count($viewDoc['views']) . '</td>';
        echo '<td>' . $number_complete . '</td>';
        echo '<td>' . $number_hs_only . '</td>';
        echo '<td>' . $number_cc_only . '</td>';
      echo '</tr>';

      switch ($org_type_list[$school_name]) {
        case 'CC':
          $cc_subtotal_views += count($viewDoc['views']);
          $cc_subtotal_complete += $number_complete;
          $cc_subtotal_hs_only += $number_hs_only;
          $cc_subtotal_cc_only += $number_cc_only;
        break;

        case 'HS':
          $hs_subtotal_views += count($viewDoc['views']);
          $hs_subtotal_complete += $number_complete;
          $hs_subtotal_hs_only += $number_hs_only;
          $hs_subtotal_cc_only += $number_cc_only;
        break;

        case 'Other':
          $other_org_subtotal_views += count($viewDoc['views']);
          $other_org_subtotal_complete += $number_complete;
          $other_org_subtotal_hs_only += $number_hs_only;
          $other_org_subtotal_cc_only += $number_cc_only;
        break;
       
        default:
        break;
      } 

      //add counts from this org to total
      $total_views += count($viewDoc['views']);
      $total_complete += $number_complete;
      $total_hs_only += $number_hs_only;
      $total_cc_only += $number_cc_only;
    }

    echo '<tr style="font-weight:bold;" class="' . $trClass . '">';
      echo '<td>CC Subtotal</td>';
      echo '<td>' . $cc_subtotal_views . '</td>';
      echo '<td>' . $cc_subtotal_complete . '</td>';
      echo '<td>' . $cc_subtotal_hs_only . '</td>';
      echo '<td>' . $cc_subtotal_cc_only . '</td>';
    echo '</tr>';
    echo '<tr style="font-weight:bold;" class="' . $trClass . '">';
      echo '<td>HS Subtotal</td>';
      echo '<td>' . $hs_subtotal_views . '</td>';
      echo '<td>' . $hs_subtotal_complete . '</td>';
      echo '<td>' . $hs_subtotal_hs_only . '</td>';
      echo '<td>' . $hs_subtotal_cc_only . '</td>';
    echo '</tr>';
    echo '<tr style="font-weight:bold;" class="' . $trClass . '">';
      echo '<td>Other Org Subtotal</td>';
      echo '<td>' . $other_org_subtotal_views . '</td>';
      echo '<td>' . $other_org_subtotal_complete . '</td>';
      echo '<td>' . $other_org_subtotal_hs_only . '</td>';
      echo '<td>' . $other_org_subtotal_cc_only . '</td>';
    echo '</tr>';
    echo '<tr style="font-weight:bold;" class="' . $trClass . '">';
      echo '<td>Total</td>';
      echo '<td>' . $total_views . '</td>';
      echo '<td>' . $total_complete . '</td>';
      echo '<td>' . $total_hs_only . '</td>';
      echo '<td>' . $total_cc_only . '</td>';
    echo '</tr>';
  echo '</table>';
echo '</div>';
  
//used in report (below)
$num_unlinked = 0; 

//only viewdrawings without a skillset are being reported
$view_drawings_no_skillset = array();

foreach($view_drawings as $view_drawing){
  if(intval($view_drawing['drawing_skillset_id']) < 1){
    $num_unlinked++;
    array_push($view_drawings_no_skillset, $view_drawing);
  }
}

usort($view_drawings_no_skillset, 'comp');

function comp($a, $b) {
  if ($a['view_school_name'] == $b['view_school_name']) {
    if($a['view_name'] == $b['view_name']){
      if($a['view_id'] == $b['view_id']){
        return strcmp($a['drawing_name'], $b['drawing_name']);
      }
      return $a['view_id'] - $b['view_id'];
    }
    return strcmp($a['view_name'], $b['view_name']);
  }
  return strcmp($a['view_school_name'], $b['view_school_name']);
}

echo '<div class="section">';
echo '<h3>Total # of unlinked Oregon Skill Set POST Drawings:</h3>';
echo '<b>Total # of unlinked Oregon Skill Set POST Drawings: '.$num_unlinked.'</b><br>';

echo '<table>';
echo '<tr class="drawing_main">';
  echo '<th>Organization Name</th>';
  echo '<th>Title of POST View</th>';
  echo '<th>titles of POST Drawings with missing Oregon Skill Set</th>';
echo '</tr>';

$trClass = new Cycler('row_light', 'row_dark');
$current_view_school_name = null;
$current_view_id = null;
$blank_cell = '<td>&nbsp;</td>';

foreach ($view_drawings_no_skillset as $view_drawing) {
  echo '<tr class="' . $trClass . '">';
    if($current_view_school_name !== $view_drawing['view_school_name']){
      $current_view_school_name = $view_drawing['view_school_name'];
      echo '<td>' . $view_drawing['view_school_name'] . '</td>';  
    } else {
      echo $blank_cell;
    }
    if($current_view_id !== $view_drawing['view_id']){
      $current_view_id = $view_drawing['view_id'];
      echo '<td><a href="/a/post_views.php?id=' . $view_drawing['view_id'] . '">' . $view_drawing['view_name'] . ' (id: ' .$view_drawing['view_id']. ')</a></td>';
    } else {
      echo $blank_cell;
    }
    
    echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $view_drawing['drawing_id'] . '">' . $view_drawing['drawing_name'] . ' (id: ' .$view_drawing['drawing_id']. ')</a></td>';
  echo '</tr>';
}
   
echo '</table>';
echo '</div>';

} else { //else !hasFeature('approved_program_name')

# Summary of Embedded POST Views
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


} //end else hasFeature('approved_program_name')

PrintFooter();




function getActiveUsers($type) {
  global $DB, $oldestActiveYear;
  return $DB->MultiQuery('
SELECT user_id, CONCAT(u.first_name, " ", u.last_name) AS name, school_name, SUM(num) AS num, MAX(last_activity) AS last_activity
FROM
(SELECT last_modified_by AS user_id, COUNT(1) AS num, MAX(last_modified) AS last_activity
FROM post_drawing_main
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
FROM post_drawing_main
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