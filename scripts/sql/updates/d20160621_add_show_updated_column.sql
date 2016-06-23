/* LL #118325033 ***********BEGIN**************/

ALTER TABLE  `drawing_main`
ADD  `show_updated` BOOLEAN NOT NULL COMMENT  'If drawing shows last updated information on published page.';
UPDATE `drawing_main` SET `show_updated` = false;

ALTER TABLE  `post_drawing_main`
ADD  `show_updated` BOOLEAN NOT NULL COMMENT  'If drawing shows last updated information on published page.';
UPDATE `post_drawing_main` SET `show_updated` = false;

/* LL #118325033 ***********END**************/