ALTER TABLE post_drawings ADD header_text TEXT;
ALTER TABLE post_drawings ADD header_link TEXT;
ALTER TABLE post_drawings ADD header_state TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE post_drawings ADD footer_state TINYINT(1) NOT NULL DEFAULT 1;
