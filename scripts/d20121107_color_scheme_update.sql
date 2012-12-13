UPDATE objects SET color='333333' WHERE objects.color='undefi' or LENGTH(objects.color) < 6;
UPDATE connections SET color='333333' WHERE connections.color='undefi' or LENGTH(connections.color) < 6;
INSERT INTO color_schemes (school_id,hex) 
(SELECT 
    drawing_main.school_id, 
    #schools.school_name as "School Name", 
    #count(*) as "#Colors Outside Scheme",
    connections.color 
FROM connections 
    INNER JOIN objects ON connections.source_object_id = objects.id
    INNER JOIN drawings on objects.drawing_id = drawings.id
    INNER JOIN drawing_main on drawings.parent_id = drawing_main.id
    INNER JOIN schools on drawing_main.school_id = schools.id
    LEFT JOIN color_schemes ON lower(connections.color)=lower(color_schemes.hex)  
        AND drawing_main.school_id = color_schemes.school_id 
WHERE 
    color_schemes.id is null
    and connections.color not in ('ffffff','333333')
    and drawings.deleted=0  
GROUP BY drawing_main.school_id, color);
INSERT INTO color_schemes (school_id,hex) 
(SELECT 
    drawing_main.school_id, 
    #schools.school_name as "School Name", 
    #count(*) as "#Colors Outside Scheme", 
    color 
FROM objects 
    INNER JOIN drawings on objects.drawing_id = drawings.id 
    INNER JOIN drawing_main on drawings.parent_id = drawing_main.id 
    INNER JOIN schools on drawing_main.school_id = schools.id 
    LEFT JOIN color_schemes ON 
        lower(objects.color) = lower(color_schemes.hex) 
        AND drawing_main.school_id = color_schemes.school_id 
WHERE 
    color_schemes.id is null 
    and objects.color not in ('ffffff','333333') 
    and drawings.deleted=0
GROUP BY drawing_main.school_id, color  
ORDER BY school_name,drawing_main.name);