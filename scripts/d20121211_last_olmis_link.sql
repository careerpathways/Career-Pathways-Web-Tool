ALTER TABLE drawing_main 
ADD COLUMN last_olmis_link VARCHAR(255), 
ADD COLUMN last_olmis_update DATETIME DEFAULT NULL;