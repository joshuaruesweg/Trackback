DROP TABLE IF EXISTS wcf1_trackback;
CREATE TABLE wcf1_trackback (
	trackbackID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	isBlocked TINYINT(1) NOT NULL DEFAULT '0',
	url TEXT NOT NULL, 
	title VARCHAR(255) NOT NULL DEFAULT '', 
	excerpt VARCHAR(255) NOT NULL DEFAULT '', 
	blogTitle VARCHAR(255) NOT NULL DEFAULT '', 
	ipAddress VARCHAR(39) NOT NULL DEFAULT '', 

	-- check 
	lastCheckTime INT(10) NOT NULL DEFAULT '0', 
	failedCount INT(1) NOT NULL DEFAULT '0'
);

DROP TABLE IF EXISTS wcf1_trackback_blacklist_entry;
CREATE TABLE wcf1_trackback_blacklist_entry (
	entryID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	host VARCHAR(255) NOT NULL,
);