+------------+-------------+
| log_action log_type   
+------------+-------------+
| delete     delete     
| upload     upload     
| protect    protect    
| block      block      
| unblock    block      
| restore    delete     
| unprotect  protect    
| rights     rights     
| move       move       
| move_redir move       
|                       
| renameuser renameuser 
| newusers   newusers   
| create     newusers   
| create2    newusers   
| modify     protect    
| overwrite  upload     
| upload     import     
| patrol     patrol     
| delete     suppress   
| autocreate newusers   
| delete     globalauth 
| whitelist  gblblock   
| dwhitelist gblblock   
| move_prot  protect    
| reblock    block      
| event      suppress   
| event      delete     
| revision   delete     
| revision   suppress   
| reblock    suppress   
| modify     abusefilter
| block      suppress   
| usergroups gblrights  
| interwiki  import     
| groupprms2 gblrights  
| config     stable     
| approve-ia review     
| approve-a  review     
| unapprove  review     
| approve    review     
| reset      stable     
| modify     stable     
| approve-i  review     
| hide-afl   suppress   
| unhide-afl suppress   
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


CREATE TABLE halfak.revert_pre_20110115(
	revision_id    INT,
	rvtd_to_id INT,
	revs_reverted  INT
);

CREATE TABLE halfak.reverted_pre_20110115(
	revision_id    INT,
	rvtg_id        INT,
	rvtd_to_id     INT,
	revs_reverted  INT
);



CREATE TABLE halfak.reverted_20110115(
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
);
INSERT INTO halfak.reverted_20110115
SELECT 
	reverted.rev_id,
	reverted.rev_user_text,
	reverted.rev_user,
	reverted.rev_comment,
	reverting.rev_id,
	reverting.rev_user_text,
	reverting.rev_user,
	reverting.rev_comment,
	reverted_to.rev_id,
	reverted_to.rev_user_text,
	reverted_to.rev_user,
	reverted_to.rev_comment,
	CONVERT(reverting.rev_comment USING utf8) REGEXP "(^revert\\ to.+using)|(^reverted\\ edits\\ by.+using)|(^reverted\\ edits\\ by.+to\\ last\\ version\\ by)|(^bot\\ -\\ rv.+to\\ last\\ version\\ by)|(-assisted\\ reversion)|(^(revert(ed)?|rv).+to\\ last)|(^undo\\ revision.+by)" OR
	CONVERT(reverting.rev_comment USING utf8) REGEXP "(\\brvv)|(\\brv[/ ]v)|(vandal(!proof|bot))|(\\b(rv|rev(ert)?|rm)\\b.*(blank|spam|nonsense|porn|mass\\sdelet|vand))",
	r.revs_reverted
FROM
	halfak.reverted_pre_20110115 r
INNER JOIN revision reverted
	ON r.revision_id = reverted.rev_id
INNER JOIN revision reverting
	ON r.revision_id = reverting.rev_id
INNER JOIN revision reverted_to
	ON r.revision_id = reverted_to.rev_id;
CREATE INDEX rev_id_idx ON halfak.reverted_20110115 (revision_id);
CREATE INDEX rvtg_id_idx ON halfak.reverted_20110115 (rvtg_id);


CREATE TABLE halfak.revert_20110115(
	revision_id    INT,
	rvtto_id     INT,
	is_vandalism   BOOL,
	revs_reverted  INT
);
INSERT INTO halfak.revert_20110115
SELECT
	rvt.revision_id,
	rvt.rvtd_to_id,
	bit_or(rvtd.is_vandalism),
	rvt.revs_reverted
FROM halfak.revert_pre_20110115 rvt
INNER JOIN halfak.reverted_20110115 rvtd
	ON rvt.revision_id = rvtd.rvtg_id
GROUP BY rvt.revision_id, rvt.rvtd_to_id, rvt.revs_reverted;
CREATE INDEX rev_id_idx ON halfak.revert_20110115 (revision_id);
CREATE INDEX is_vandalism ON halfak.revert_20110115 (is_vandalism);




--SELECT * FROM revision WHERE rev_comment LIKE "Requesting speedy deletion%"


SELECT 
	SUBSTRING(rev_timestamp, 1,4)                           as year, 
	rev_user                                                as user_id,
	u.user_name                                             as username,
	COUNT(*)                                                as revisions, 
	SUM(rvt.revision_id IS NOT NULL)                        as reverts, 
	SUM(rvt.revision_id IS NOT NULL AND rvt.is_vandalism)   as vandal_reverts
FROM revision r
LEFT JOIN halfak.revert_20100130 rvt
	ON r.rev_id = rvt.revision_id
INNER JOIN user u
	ON r.rev_user = u.user_id
WHERE rev_timestamp < "20110000000000"
GROUP BY SUBSTRING(rev_timestamp, 1,4), rev_user, u.user_name

