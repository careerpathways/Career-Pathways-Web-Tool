ALTER TABLE post_drawings MODIFY header_text TEXT;
ALTER TABLE post_drawings MODIFY footer_text TEXT;

UPDATE post_drawings
SET header_text = CONCAT('<a href="',header_link,'" target="_blank">',header_text,'</a>')
WHERE header_link is not null and header_link != '';

UPDATE post_drawings
SET footer_text = CONCAT('<a href="',footer_link,'" target="_blank">',footer_text,'</a>')
WHERE footer_link is not null and footer_link != '';

ALTER TABLE drawing_main ADD INDEX idx_school_id(school_id);
ALTER TABLE post_drawing_main ADD INDEX idx_school_id(school_id);
ALTER TABLE drawings ADD INDEX idx_parent_id(parent_id);
ALTER TABLE post_drawings ADD INDEX idx_parent_id(parent_id);
ALTER TABLE objects ADD INDEX idx_color(color);
ALTER TABLE objects ADD INDEX idx_drawing_id(drawing_id);
ALTER TABLE connections ADD INDEX idx_color(color);
ALTER TABLE connections ADD INDEX idx_source_object_id(source_object_id);
ALTER TABLE connections ADD INDEX idx_destination_object_id(destination_object_id);
ALTER TABLE users ADD INDEX idx_school_id(school_id);
ALTER TABLE vpost_views ADD INDEX idx_school_id(school_id);
ALTER TABLE post_row ADD INDEX idx_drawing_id(drawing_id);
ALTER TABLE post_default_row ADD INDEX idx_school_id(school_id);
ALTER TABLE post_default_col ADD INDEX idx_school_id(school_id);
ALTER TABLE post_col ADD INDEX idx_drawing_id(drawing_id);
ALTER TABLE post_cell ADD INDEX idx_drawing_id(drawing_id);
ALTER TABLE post_cell ADD INDEX idx_row_id(row_id);
ALTER TABLE post_cell ADD INDEX idx_col_id(col_id);