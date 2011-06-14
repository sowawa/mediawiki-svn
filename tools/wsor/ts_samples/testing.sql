CREATE TABLE u_grphack.user_meta (
	user_id      INT,
	username     VARCHAR(255),
	registration VARCHAR(14),
	reg_year     INT
);

INSERT INTO u_grphack.user_meta
SELECT user_id, user_name, user_registration, SUBSTRING(user_registration, 1,4)
FROM user;

CREATE INDEX user_meta_pkey ON u_grphack.user_meta (user_id) USING BTREE;
CREATE INDEX user_meta_reg_year ON u_grphack.user_meta (reg_year) USING BTREE;




explain SELECT * FROM u_grphack.user_meta
WHERE reg_year = 2004
ORDER BY RAND();

explain SELECT * FROM user
WHERE user_registration BETWEEN "20040000000000" AND "20041231115959"
ORDER BY RAND()
LIMIT 10;


CREATE TABLE halfak.user_meta (
	user_id    INT, 
	first_edit VARBINARY(14),
	last_edit  VARBINARY(14)
)

INSERT INTO halfak.user_meta
SELECT rev_user, min(rev_timestamp), max(rev_timestamp)
FROM revision 
WHERE rev_user IS NOT NULL
GROUP BY rev_user;

CREATE UNIQUE INDEX user_id_idx ON halfak.user_meta (user_id);
CREATE INDEX first_edit_idx ON halfak.user_meta (first_edit);
CREATE INDEX last_edit_idx ON halfak.user_meta (last_edit);
