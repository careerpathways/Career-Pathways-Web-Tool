/*#78***********BEGIN**************/
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  id INT NOT NULL AUTO_INCREMENT,
  name varchar(255),
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
INSERT INTO roles (name) SELECT name FROM signature_categories ORDER BY id ASC;

DROP TABLE IF EXISTS users_roles;
CREATE TABLE users_roles (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX(user_id, role_id)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
INSERT INTO users_roles (user_id, role_id) SELECT user_id, category_id from signature_categories_users;

DROP TABLE IF EXISTS assurances;
CREATE TABLE assurances (
        id INT NOT NULL AUTO_INCREMENT,
        vpost_view_id INT,
        created_date DATETIME,
  last_signed_date DATETIME,
        valid BOOLEAN NOT NULL DEFAULT TRUE,
        PRIMARY KEY (`id`),
        INDEX(vpost_view_id)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
INSERT INTO assurances (vpost_view_id, created_date, valid) SELECT id, NOW(), TRUE from vpost_views;

DROP TABLE IF EXISTS assurance_requirements_ct;
CREATE TABLE assurance_requirements_ct (
        id INT NOT NULL AUTO_INCREMENT,
        assurance_id INT,
        requirement_id INT,
  user_id INT,
  date_signed DATETIME,
        PRIMARY KEY (`id`)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
INSERT INTO assurance_requirements_ct (assurance_id, requirement_id, user_id, date_signed) SELECT (SELECT id FROM assurances where assurances.vpost_view_id=signatures.vpost_view_id), category_id, user_id, date_signed FROM signatures;

UPDATE assurances
INNER JOIN (SELECT assurance_requirements_ct.assurance_id as 'assurance_id',
                  MAX(assurance_requirements_ct.date_signed) AS 'max_signed'
           FROM assurance_requirements_ct
           GROUP BY assurance_requirements_ct.assurance_id
) as temp ON assurances.id=temp.assurance_id
SET last_signed_date = temp.max_signed;

DROP TABLE IF EXISTS requirements;
CREATE TABLE requirements (
        id INT NOT NULL AUTO_INCREMENT,
    requirement_type VARCHAR(255),
        description TEXT,
        required_role INT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
/*Existing Stakeholder Requirements*/
insert into requirements (requirement_type, description) (SELECT 'stakeholder', name from signature_categories ORDER BY signature_categories.id) ;
UPDATE requirements set required_role = id;
/*Minimum Criteria*/
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','The secondary CTE, academic, and appropriate elective courses are included, as well as the state and local graduation requirements.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','The secondary Program of Study includes leadership standards.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','The secondary Program of Study includes employability standards, where appropriate.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','The Program of Study includes coherent and rigorous coursework in a non- duplicative sequence of courses from secondary to postsecondary.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','Completion of the secondary Program of Study prepares students for entry into the postsecondary program or apprenticeship.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','Program of Study courses include appropriate state standards and/or industry skills standards.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('minimum','Program of Study leads to an industry recognized credential; academic certificate or degree; or employment.',3);
/*Exceeds Minimum Criteria*/
insert into requirements (requirement_type, description,required_role) VALUES ('extra','There is a dual credit articulation agreement on file for one or more courses in the secondary/postsecondary Program of Study.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('extra','The Program of Study includes multiple entry and/or exit points at the post- secondary level.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('extra','The Program of Study offers course work and skill development for self- employment and/or entrepreneurial opportunities.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('extra','The Program of Study is linked to a comprehensive school counseling program, such as Navigation 101.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('extra','There is program alignment between the community and technical college Program of Study and a baccalaureate program, with a signed articulation agreement on file.',3);
insert into requirements (requirement_type, description,required_role) VALUES ('extra','The Program of Study is linked to a skill panel or a Center of Excellence.',3);
/*#78***********END**************/

/*#79***********BEGIN**************/
INSERT INTO admin_module
  (name,friendly_name,page_title,active,`order`,feature)
  VALUES('view_requirements', 'Edit Assurance Criteria', 'Edit Assurance Criteria', TRUE, 236, 'post_assurances');
INSERT INTO admin_level_module (module_id, `level`) VALUES(LAST_INSERT_ID(),127);
/*#79***********END**************/

/*#82***********BEGIN**************/
ALTER TABLE  `vpost_views` ADD  `published` BOOLEAN DEFAULT FALSE;
UPDATE `vpost_views` set `published`=TRUE;
/*#82***********END**************/


/*ASSURANCE PERMISSION UPDATE**************/
SET @id = (SELECT id FROM admin_module where name='post_assurance');
UPDATE admin_level_module SET `level`=4 WHERE module_id=@id;
/*ASSURANCE PERMISSION UPDATE**************/

