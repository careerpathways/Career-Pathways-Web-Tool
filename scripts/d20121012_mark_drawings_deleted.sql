UPDATE drawings
JOIN drawing_main on drawings.parent_id = drawing_main.id
LEFT JOIN schools on drawing_main.school_id = schools.id
SET drawings.deleted=1
WHERE schools.id is null;

UPDATE post_drawings
JOIN post_drawing_main on post_drawings.parent_id = post_drawing_main.id
LEFT JOIN schools on post_drawing_main.school_id = schools.id
SET post_drawings.deleted=1
WHERE schools.id is null;