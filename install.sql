
CREATE TABLE IF NOT EXISTS `webcp_loginrate`
(
	`ip_prefix`  VARCHAR(63) NOT NULL,
	`attempts`   TEXT        NOT NULL,
	`last_hit`   INTEGER     NOT NULL,

	PRIMARY KEY (`ip_prefix`)
);
