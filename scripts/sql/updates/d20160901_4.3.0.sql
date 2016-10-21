/* LL #120235713 ***********BEGIN**************/

ALTER TABLE  `drawing_main`
ADD  `show_pdf_ada_links` BOOLEAN NOT NULL COMMENT  'If drawing shows PDF and ADA accessible links at top-right corner of drawing.';
UPDATE `drawing_main` SET `show_pdf_ada_links` = false;

ALTER TABLE  `post_drawing_main`
ADD  `show_pdf_ada_links` BOOLEAN NOT NULL COMMENT  'If drawing shows PDF and ADA accessible links at top-right corner of drawing.';
UPDATE `post_drawing_main` SET `show_pdf_ada_links` = false;

/* LL #120235713 ***********END**************/