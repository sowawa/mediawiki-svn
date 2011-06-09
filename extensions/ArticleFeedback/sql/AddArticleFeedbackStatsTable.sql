DROP TABLE IF EXISTS article_feedback_stats;
CREATE TABLE IF NOT EXISTS /*_*/article_feedback_stats (
	afs_page_id integer unsigned NOT NULL,
	-- data point to be used for ordering this data
	afs_orderable_data double unsigned NOT NULL,
	-- json object of stat data
	afs_data varbinary(255) NOT NULL,
	afs_stats_type_id integer unsigned NOT NULL,
	-- timestamp of insertion job	
	afs_ts binary(14) NOT NULL
) /*$wgDBTableOptions*/;
CREATE UNIQUE INDEX /*i*/ afs_page_ts_type ON /*_*/article_feedback_stats( afs_page_id, afs_ts, afs_stats_type_id );
CREATE INDEX /*i*/ afs_ts_avg_overall ON /*_*/article_feedback_stats (afs_ts, afs_orderable_data);
