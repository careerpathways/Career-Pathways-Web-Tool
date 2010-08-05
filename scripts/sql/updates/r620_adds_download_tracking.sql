ALTER TABLE guest_logins ADD COLUMN download TINYINT(4) NOT NULL DEFAULT '0' AFTER referral;
