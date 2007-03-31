<?php

$messages = array(
	'en' => array(
		'inplace_access_disabled' => 'Access to this service has been disabled for all clients.',
		'inplace_access_denied' => 'This service is restricted by client IP.',
		'inplace_scaler_no_temp' => 'No valid temporary directory, set $wgLocalTmpDirectory to a writeable directory.',
		'inplace_scaler_not_enough_params' => 'Not enough parameters.',
		'inplace_scaler_invalid_image' => 'Invalid image, could not determine size.',
		'inplace_scaler_failed' => 'An error was encountered during image scaling: $1',

		'webstore_access' => 'This service is restricted by client IP.',
		'webstore_path_invalid' => 'The filename was invalid.',
		'webstore_dest_open' => 'Unable to open destination file.',
		'webstore_dest_lock' => 'Failed to get lock on destination file.',
		'webstore_dest_mkdir' => 'Unable to create directory.',
		'webstore_archive_lock' => 'Failed to get lock on archive file.',
		'webstore_archive_mkdir' => 'Unable to create directory.',
		'webstore_src_open' => 'Unable to open source file.',
		'webstore_src_close' => 'Error closing source file.',
		'webstore_src_delete' => 'Error deleting source file.',

		'webstore_rename' => 'Error renaming file.',
		'webstore_lock_open' => 'Error opening lock file.',
		'webstore_lock_close' => 'Error closing lock file.',
		'webstore_dest_exists' => 'Error, destination file exists.',
		'webstore_temp_open' => 'Error opening temporary file.',
		'webstore_temp_copy' => 'Error copying to destination file.',
		'webstore_temp_close' => 'Error closing temporary file.',
		'webstore_temp_lock' => 'Error locking temporary file.',

		'webstore_no_file' => 'No file was uploaded.',
		'webstore_move_uploaded' => 'Error moving uploaded file.',

		'webstore_invalid_repository' => 'Invalid repository.',

		'webstore_no_deleted' => 'No archive directory for deleted files is defined.',
		'webstore_curl' => 'Error from cURL: $1',
		'webstore_404' => 'File not found.',
	),
);

?>
