ALTER TABLE assets ADD active BOOLEAN NOT NULL AFTER created_by;
UPDATE assets SET active = true;
