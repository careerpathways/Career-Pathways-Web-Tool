ALTER TABLE  `connections` ADD  `thickness` TEXT NOT NULL AFTER  `destination_position`;
# Default thickness value is 5
UPDATE `connections` SET `thickness` = '5';

ALTER TABLE  `connections` ADD  `lineDashStyle` TEXT NOT NULL AFTER  `thickness`;
# Default line_dash_style is "solid"
UPDATE `connections` SET `lineDashStyle` = 'solid';


ALTER TABLE  `post_sidebar_options` ADD  `abbreviation` TEXT NOT NULL AFTER  `text`;
