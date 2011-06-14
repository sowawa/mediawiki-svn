+------------+-------------+
| log_action | log_type    |
+------------+-------------+
| delete     | delete      |
| upload     | upload      |
| protect    | protect     |
| block      | block       |
| unblock    | block       |
| restore    | delete      |
| unprotect  | protect     |
| rights     | rights      |
| move       | move        |
| move_redir | move        |
|            |             |
| renameuser | renameuser  |
| newusers   | newusers    |
| create     | newusers    |
| create2    | newusers    |
| modify     | protect     |
| overwrite  | upload      |
| upload     | import      |
| patrol     | patrol      |
| delete     | suppress    |
| autocreate | newusers    |
| delete     | globalauth  |
| whitelist  | gblblock    |
| dwhitelist | gblblock    |
| move_prot  | protect     |
| reblock    | block       |
| event      | suppress    |
| event      | delete      |
| revision   | delete      |
| revision   | suppress    |
| reblock    | suppress    |
| modify     | abusefilter |
| block      | suppress    |
| usergroups | gblrights   |
| interwiki  | import      |
| groupprms2 | gblrights   |
| config     | stable      |
| approve-ia | review      |
| approve-a  | review      |
| unapprove  | review      |
| approve    | review      |
| reset      | stable      |
| modify     | stable      |
| approve-i  | review      |
| hide-afl   | suppress    |
| unhide-afl | suppress    |
+------------+-------------+
46 rows in set (2 min 28.77 sec)

SELECT log_user, count(*) 
FROM logging l
WHERE log_action = "patrol"
AND log_type = "patrol"

SELECT SUBSTRING(log_timestamp,1,4) as year, count(*) as count
FROM logging l
WHERE log_action = "patrol"
AND log_type = "patrol"
GROUP BY SUBSTRING(log_timestamp,1,4);


SELECT  p.page_id, p.page_title, count(*)
FROM logging l
INNER JOIN page p
ON l.log_page = p.page_id
WHERE log_action = "suppress"
AND log_timestamp BETWEEN "20040000000000" AND "20049999999999"
GROUP BY p.page_id, p.page_title;



CREATE TABLE halfak.reverted_20100130(
	revision_id     INT,
	username        VARBINARY(255),
	user_id         INT,
	comment         VARBINARY(255),
	rvtg_id         INT,
	rvtg_username   VARBINARY(255),
	rvtg_user_id    INT,
	rvtg_comment    VARBINARY(255),
	rvtto_id        INT,
	rvtto_username  VARBINARY(255),
	rvtto_user_id   INT,
	rvtto_comment   VARBINARY(255),
	is_vandalism    BOOL,
	revs_reverted   INT
)


--SELECT * FROM revision WHERE rev_comment LIKE "Requesting speedy deletion%"


