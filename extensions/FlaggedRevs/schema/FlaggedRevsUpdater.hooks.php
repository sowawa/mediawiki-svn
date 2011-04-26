<?php
/**
 * Class containing updater functions for a FlaggedRevs environment
 */
class FlaggedRevsUpdaterHooks {
	public static function addSchemaUpdates( DatabaseUpdater $du ) {
		global $wgDBtype;
		if ( $wgDBtype == 'mysql' ) {
			$base = dirname( __FILE__ ) . '/mysql';
			// Initial install tables (current schema)
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.sql", true ) );
			// Updates (in order)...
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_expiry', "$base/patch-fpc_expiry.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpage_config', 'fpc_expiry', "$base/patch-expiry-index.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_promote', "$base/patch-flaggedrevs_promote.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpages', "$base/patch-flaggedpages.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedrevs', 'fr_img_name', "$base/patch-fr_img_name.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_tracking', "$base/patch-flaggedrevs_tracking.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpages', 'fp_pending_since', "$base/patch-fp_pending_since.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_level', "$base/patch-fpc_level.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpage_pending', "$base/patch-flaggedpage_pending.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_stats', "$base/patch-flaggedrevs_stats.sql", true ) );
			$du->addExtensionUpdate( array( 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/patch-fi_img_timestamp.sql" ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedrevs', 'page_rev', "$base/patch-fr_page_rev-index.sql", true ) );
		} elseif ( $wgDBtype == 'postgres' ) {
			$base = dirname( __FILE__ ) . '/postgres';
			// Initial install tables (current schema)
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.pg.sql", true ) );
			// Updates (in order)...
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_expiry', "TIMESTAMPTZ NULL" ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpage_config', 'fpc_expiry', "$base/patch-expiry-index.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_promote', "$base/patch-flaggedrevs_promote.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpages', "$base/patch-flaggedpages.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedrevs', 'fr_img_sha1', "$base/patch-fr_img_name.sql", true ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs_tracking', "$base/patch-flaggedrevs_tracking.sql", true ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedpages', 'fp_pending_since', "$base/patch-fp_pending_since.sql", true ) );
			$du->addExtensionUpdate( array( 'addField',
				'flaggedpage_config', 'fpc_level', "TEXT NULL" ) );
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedpage_pending', "$base/patch-flaggedpage_pending.sql", true ) );
			// @TODO: PG stats table???
			$du->addExtensionUpdate( array( 'FlaggedRevsUpdaterHooks::doFlaggedImagesTimestampNULL',
				"$base/patch-fi_img_timestamp.sql" ) );
			$du->addExtensionUpdate( array( 'addIndex',
				'flaggedrevs', 'page_rev', "$base/patch-fr_page_rev-index.sql", true ) );
		} elseif ( $wgDBtype == 'sqlite' ) {
			$base = dirname( __FILE__ ) . '/mysql';
			$du->addExtensionUpdate( array( 'addTable',
				'flaggedrevs', "$base/FlaggedRevs.sql", true ) );
		}
		return true;
	}

	public static function doFlaggedImagesTimestampNULL( $du, $patch ) {
		$info = $du->getDB()->fieldInfo( 'flaggedimages', 'fi_img_timestamp' );
		if ( $info->isNullable() ) {
			$du->output( "...fi_img_timestamp is already nullable.\n" );
			return;
		}
		$du->output( "Making fi_img_timestamp nullable... " );
		$du->getDB()->sourceFile( $patch );
		$du->output( "done.\n" );
	}
}