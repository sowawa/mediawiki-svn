<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @ingroup Media
 * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
 * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason, 2009 Brent Garber
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @see http://exif.org/Exif2-2.PDF The Exif 2.2 specification
 * @file
 */

/**
 * Class to extract and validate Exif data from jpeg (and possibly tiff) files.
 * @ingroup Media
 */
class Exif {

	const BYTE      = 1;    //!< An 8-bit (1-byte) unsigned integer.
	const ASCII     = 2;    //!< An 8-bit byte containing one 7-bit ASCII code. The final byte is terminated with NULL.
	const SHORT     = 3;    //!< A 16-bit (2-byte) unsigned integer.
	const LONG      = 4;    //!< A 32-bit (4-byte) unsigned integer.
	const RATIONAL  = 5;    //!< Two LONGs. The first LONG is the numerator and the second LONG expresses the denominator
	const UNDEFINED = 7;    //!< An 8-bit byte that can take any value depending on the field definition
	const SLONG     = 9;    //!< A 32-bit (4-byte) signed integer (2's complement notation),
	const SRATIONAL = 10;   //!< Two SLONGs. The first SLONG is the numerator and the second SLONG is the denominator.
	const IGNORE    = -1;   // A fake value for things we don't want or don't support.

	//@{
	/* @var array
	 * @private
	 */

	/**
	 * Exif tags grouped by category, the tagname itself is the key and the type
	 * is the value, in the case of more than one possible value type they are
	 * separated by commas.
	 */
	var $mExifTags;

	/**
	 * The raw Exif data returned by exif_read_data()
	 */
	var $mRawExifData;

	/**
	 * A Filtered version of $mRawExifData that has been pruned of invalid
	 * tags and tags that contain content they shouldn't contain according
	 * to the Exif specification
	 */
	var $mFilteredExifData;

	/**
	 * Filtered and formatted Exif data, see FormatExif::getFormattedData()
	 */
	var $mFormattedExifData;

	//@}

	//@{
	/* @var string
	 * @private
	 */

	/**
	 * The file being processed
	 */
	var $file;

	/**
	 * The basename of the file being processed
	 */
	var $basename;

	/**
	 * The private log to log to, e.g. 'exif'
	 */
	var $log = false;

	//@}

	/**
	 * Constructor
	 *
	 * @param $file String: filename.
	 * @fixme the following are broke:
	 * SubjectArea. Need to test the more obscure tags.
	 *
	 * DigitalZoomRatio = 0/0 is rejected. need to determine if thats valid.
	 * possibly should treat 0/0 = 0. need to read exif spec on that.
	 */
	function __construct( $file ) {
		/**
		 * Page numbers here refer to pages in the EXIF 2.2 standard
		 *
		 * Note, Exif::UNDEFINED is treated as a string, not as an array of bytes
		 * so don't put a count parameter for any UNDEFINED values.
		 *
		 * @link http://exif.org/Exif2-2.PDF The Exif 2.2 specification
		 */
		$this->mExifTags = array(
			# TIFF Rev. 6.0 Attribute Information (p22)
			'IFD0' => array(
				# Tags relating to image structure
				'ImageWidth' => Exif::SHORT.','.Exif::LONG,		# Image width
				'ImageLength' => Exif::SHORT.','.Exif::LONG,		# Image height
				'BitsPerSample' => array( Exif::SHORT, 3 ),		# Number of bits per component
				# "When a primary image is JPEG compressed, this designation is not"
				# "necessary and is omitted." (p23)
				'Compression' => Exif::SHORT,				# Compression scheme #p23
				'PhotometricInterpretation' => Exif::SHORT,		# Pixel composition #p23
				'Orientation' => Exif::SHORT,				# Orientation of image #p24
				'SamplesPerPixel' => Exif::SHORT,			# Number of components
				'PlanarConfiguration' => Exif::SHORT,			# Image data arrangement #p24
				'YCbCrSubSampling' => array( Exif::SHORT, 2),		# Subsampling ratio of Y to C #p24
				'YCbCrPositioning' => Exif::SHORT,			# Y and C positioning #p24-25
				'XResolution' => Exif::RATIONAL,			# Image resolution in width direction
				'YResolution' => Exif::RATIONAL,			# Image resolution in height direction
				'ResolutionUnit' => Exif::SHORT,			# Unit of X and Y resolution #(p26)

				# Tags relating to recording offset
				'StripOffsets' => Exif::SHORT.','.Exif::LONG,			# Image data location
				'RowsPerStrip' => Exif::SHORT.','.Exif::LONG,			# Number of rows per strip
				'StripByteCounts' => Exif::SHORT.','.Exif::LONG,		# Bytes per compressed strip
				'JPEGInterchangeFormat' => Exif::SHORT.','.Exif::LONG,		# Offset to JPEG SOI
				'JPEGInterchangeFormatLength' => Exif::SHORT.','.Exif::LONG,	# Bytes of JPEG data

				# Tags relating to image data characteristics
				'TransferFunction' => Exif::SHORT,			# Transfer function
				'WhitePoint' => array( Exif::RATIONAL, 2),		# White point chromaticity
				'PrimaryChromaticities' => array( Exif::RATIONAL, 6),	# Chromaticities of primarities
				'YCbCrCoefficients' => array( Exif::RATIONAL, 3),	# Color space transformation matrix coefficients #p27
				'ReferenceBlackWhite' => array( Exif::RATIONAL, 6),	# Pair of black and white reference values

				# Other tags
				'DateTime' => Exif::ASCII,				# File change date and time
				'ImageDescription' => Exif::ASCII,			# Image title
				'Make' => Exif::ASCII,					# Image input equipment manufacturer
				'Model' => Exif::ASCII,					# Image input equipment model
				'Software' => Exif::ASCII,				# Software used
				'Artist' => Exif::ASCII,				# Person who created the image
				'Copyright' => Exif::ASCII,				# Copyright holder
			),

			# Exif IFD Attribute Information (p30-31)
			'EXIF' => array(
				# TODO: NOTE: Nonexistence of this field is taken to mean nonconformance
				# to the EXIF 2.1 AND 2.2 standards
				'ExifVersion' =>  Exif::UNDEFINED,			# Exif version
				'FlashPixVersion' => Exif::UNDEFINED,			# Supported Flashpix version #p32

				# Tags relating to Image Data Characteristics
				'ColorSpace' => Exif::SHORT,				# Color space information #p32

				# Tags relating to image configuration
				'ComponentsConfiguration' => Exif::UNDEFINED,			# Meaning of each component #p33
				'CompressedBitsPerPixel' => Exif::RATIONAL,			# Image compression mode
				'PixelYDimension' => Exif::SHORT.','.Exif::LONG,		# Valid image width
				'PixelXDimension' => Exif::SHORT.','.Exif::LONG,		# Valid image height

				# Tags relating to related user information
				'MakerNote' => Exif::IGNORE,				# Manufacturer notes
				'UserComment' => Exif::UNDEFINED,			# User comments #p34

				# Tags relating to related file information
				'RelatedSoundFile' => Exif::ASCII,			# Related audio file

				# Tags relating to date and time
				'DateTimeOriginal' => Exif::ASCII,			# Date and time of original data generation #p36
				'DateTimeDigitized' => Exif::ASCII,			# Date and time of original data generation
				'SubSecTime' => Exif::ASCII,				# DateTime subseconds
				'SubSecTimeOriginal' => Exif::ASCII,			# DateTimeOriginal subseconds
				'SubSecTimeDigitized' => Exif::ASCII,			# DateTimeDigitized subseconds

				# Tags relating to picture-taking conditions (p31)
				'ExposureTime' => Exif::RATIONAL,			# Exposure time
				'FNumber' => Exif::RATIONAL,				# F Number
				'ExposureProgram' => Exif::SHORT,			# Exposure Program #p38
				'SpectralSensitivity' => Exif::ASCII,			# Spectral sensitivity
				'ISOSpeedRatings' => Exif::SHORT,			# ISO speed rating
				'OECF' => Exif::IGNORE,
				# Optoelectronic conversion factor. Note: We don't have support for this atm.
				'ShutterSpeedValue' => Exif::SRATIONAL,			# Shutter speed
				'ApertureValue' => Exif::RATIONAL,			# Aperture
				'BrightnessValue' => Exif::SRATIONAL,			# Brightness
				'ExposureBiasValue' => Exif::SRATIONAL,			# Exposure bias
				'MaxApertureValue' => Exif::RATIONAL,			# Maximum land aperture
				'SubjectDistance' => Exif::RATIONAL,			# Subject distance
				'MeteringMode' => Exif::SHORT,				# Metering mode #p40
				'LightSource' => Exif::SHORT,				# Light source #p40-41
				'Flash' => Exif::SHORT,					# Flash #p41-42
				'FocalLength' => Exif::RATIONAL,			# Lens focal length
				'SubjectArea' => array( Exif::SHORT, 4 ),		# Subject area
				'FlashEnergy' => Exif::RATIONAL,			# Flash energy
				'SpatialFrequencyResponse' => Exif::IGNORE,		# Spatial frequency response. Not supported atm.
				'FocalPlaneXResolution' => Exif::RATIONAL,		# Focal plane X resolution
				'FocalPlaneYResolution' => Exif::RATIONAL,		# Focal plane Y resolution
				'FocalPlaneResolutionUnit' => Exif::SHORT,		# Focal plane resolution unit #p46
				'SubjectLocation' => array( Exif::SHORT, 2),		# Subject location
				'ExposureIndex' => Exif::RATIONAL,			# Exposure index
				'SensingMethod' => Exif::SHORT,				# Sensing method #p46
				'FileSource' => Exif::UNDEFINED,			# File source #p47
				'SceneType' => Exif::UNDEFINED,				# Scene type #p47
				'CFAPattern' => Exif::IGNORE,				# CFA pattern. not supported atm.
				'CustomRendered' => Exif::SHORT,			# Custom image processing #p48
				'ExposureMode' => Exif::SHORT,				# Exposure mode #p48
				'WhiteBalance' => Exif::SHORT,				# White Balance #p49
				'DigitalZoomRatio' => Exif::RATIONAL,			# Digital zoom ration
				'FocalLengthIn35mmFilm' => Exif::SHORT,			# Focal length in 35 mm film
				'SceneCaptureType' => Exif::SHORT,			# Scene capture type #p49
				'GainControl' => Exif::SHORT,				# Scene control #p49-50
				'Contrast' => Exif::SHORT,				# Contrast #p50
				'Saturation' => Exif::SHORT,				# Saturation #p50
				'Sharpness' => Exif::SHORT,				# Sharpness #p50
				'DeviceSettingDescription' => Exif::IGNORE,
				# Desice settings description. This could maybe be supported. Need to find an
				# example file that uses this to see if it has stuff of interest in it.
				'SubjectDistanceRange' => Exif::SHORT,			# Subject distance range #p51

				'ImageUniqueID' => Exif::ASCII,				# Unique image ID
			),

			# GPS Attribute Information (p52)
			'GPS' => array(
				'GPSVersion' => Exif::UNDEFINED,
				# Should be an array of 4 Exif::BYTE's. However php treats it as an undefined
				# Note exif standard calls this GPSVersionID, but php doesn't like the id suffix
				'GPSLatitudeRef' => Exif::ASCII,			# North or South Latitude #p52-53
				'GPSLatitude' => array( Exif::RATIONAL, 3 ),		# Latitude
				'GPSLongitudeRef' => Exif::ASCII,			# East or West Longitude #p53
				'GPSLongitude' => array( Exif::RATIONAL, 3),		# Longitude
				'GPSAltitudeRef' => Exif::UNDEFINED,
				# Altitude reference. Note, the exif standard says this should be an EXIF::Byte,
				# but php seems to disagree.
				'GPSAltitude' => Exif::RATIONAL,			# Altitude
				'GPSTimeStamp' => array( Exif::RATIONAL, 3),		# GPS time (atomic clock)
				'GPSSatellites' => Exif::ASCII,				# Satellites used for measurement
				'GPSStatus' => Exif::ASCII,				# Receiver status #p54
				'GPSMeasureMode' => Exif::ASCII,			# Measurement mode #p54-55
				'GPSDOP' => Exif::RATIONAL,				# Measurement precision
				'GPSSpeedRef' => Exif::ASCII,				# Speed unit #p55
				'GPSSpeed' => Exif::RATIONAL,				# Speed of GPS receiver
				'GPSTrackRef' => Exif::ASCII,				# Reference for direction of movement #p55
				'GPSTrack' => Exif::RATIONAL,				# Direction of movement
				'GPSImgDirectionRef' => Exif::ASCII,			# Reference for direction of image #p56
				'GPSImgDirection' => Exif::RATIONAL,			# Direction of image
				'GPSMapDatum' => Exif::ASCII,				# Geodetic survey data used
				'GPSDestLatitudeRef' => Exif::ASCII,			# Reference for latitude of destination #p56
				'GPSDestLatitude' => array( Exif::RATIONAL, 3 ),	# Latitude destination
				'GPSDestLongitudeRef' => Exif::ASCII,			# Reference for longitude of destination #p57
				'GPSDestLongitude' => array( Exif::RATIONAL, 3 ),	# Longitude of destination
				'GPSDestBearingRef' => Exif::ASCII,			# Reference for bearing of destination #p57
				'GPSDestBearing' => Exif::RATIONAL,			# Bearing of destination
				'GPSDestDistanceRef' => Exif::ASCII,			# Reference for distance to destination #p57-58
				'GPSDestDistance' => Exif::RATIONAL,			# Distance to destination
				'GPSProcessingMethod' => Exif::UNDEFINED,		# Name of GPS processing method
				'GPSAreaInformation' => Exif::UNDEFINED,		# Name of GPS area
				'GPSDateStamp' => Exif::ASCII,				# GPS date
				'GPSDifferential' => Exif::SHORT,			# GPS differential correction
			),
		);

		$this->file = $file;
		$this->basename = wfBaseName( $this->file );

		$this->debugFile( $this->basename, __FUNCTION__, true );
		wfSuppressWarnings();
		$data = exif_read_data( $this->file, 0, true );
		wfRestoreWarnings();
		/**
		 * exif_read_data() will return false on invalid input, such as
		 * when somebody uploads a file called something.jpeg
		 * containing random gibberish.
		 */
		$this->mRawExifData = $data ? $data : array();
		$this->makeFilteredData();
		$this->collapseData();
		$this->debugFile( __FUNCTION__, false );
	}

	/**
	 * Make $this->mFilteredExifData
	 */
	function makeFilteredData() {
		$this->mFilteredExifData = Array();

		foreach ( array_keys( $this->mRawExifData ) as $section ) {
			if ( !in_array( $section, array_keys( $this->mExifTags ) ) ) {
				$this->debug( $section , __FUNCTION__, "'$section' is not a valid Exif section" );
				continue;
			}

			foreach ( array_keys( $this->mRawExifData[$section] ) as $tag ) {
				if ( !in_array( $tag, array_keys( $this->mExifTags[$section] ) ) ) {
					$this->debug( $tag, __FUNCTION__, "'$tag' is not a valid tag in '$section'" );
					continue;
				}

				$this->mFilteredExifData[$tag] = $this->mRawExifData[$section][$tag];
				// This is ok, as the tags in the different sections do not conflict.
				// except in computed and thumbnail section, which we don't use.

				$value = $this->mRawExifData[$section][$tag];
				if ( !$this->validate( $section, $tag, $value ) ) {
					$this->debug( $value, __FUNCTION__, "'$tag' contained invalid data" );
					unset( $this->mFilteredExifData[$tag] );
				}
			}
		}
	}

	/**
	* Collapse some fields together.
	* This converts some fields from exif form, to a more friendly form.
	* For example GPS lattitude to a single number.
	*
	* The rationale behind this is that we're storing data, not presenting to the user
	* For example a longitude is a single number describing how far away you are from
	* the prime meridian. Well it might be nice to split it up into minutes and seconds
	* for the user, it doesn't really make sense to split a single number into 4 parts
	* for storage. (degrees, minutes, second, direction vs single floating point number).
	*
	* Other things this might do (not really sure if they make sense or not):
	* Dates -> mediawiki date format.
	* convert values that can be in different units to be in one standardized unit.
	*
	* As an alternative approach, some of this could be done in the validate phase
	* if we make up our own types like Exif::DATE.
	*/
	function collapseData( ) {

		$this->exifGPStoNumber( 'GPSLatitude' );
		$this->exifGPStoNumber( 'GPSDestLatitude' );
		$this->exifGPStoNumber( 'GPSLongitude' );
		$this->exifGPStoNumber( 'GPSDestLongitude' );

		if ( isset( $this->mFilteredExifData['GPSAltitude'] ) && isset( $this->mFilteredExifData['GPSAltitudeRef'] ) ) {
			if ( $this->mFilteredExifData['GPSAltitudeRef'] === "\1" ) {
				$this->mFilteredExifData['GPSAltitude'] *= - 1;
			}
			unset( $this->mFilteredExifData['GPSAltitudeRef'] );
		}

		$this->exifPropToOrd( 'FileSource' );
		$this->exifPropToOrd( 'SceneType' );

		$this->charCodeString( 'UserComment' );
		$this->charCodeString( 'GPSProcessingMethod');
		$this->charCodeString( 'GPSAreaInformation' );
		
		//ComponentsConfiguration should really be an array instead of a string...
		//This turns a string of binary numbers into an array of numbers.

		if ( isset ( $this->mFilteredExifData['ComponentsConfiguration'] ) ) {
			$val = $this->mFilteredExifData['ComponentsConfiguration'];
			$ccVals = array();
			for ($i = 0; $i < strlen($val); $i++) {
				$ccVals[$i] = ord( substr($val, $i, 1) );
			}
			$ccVals['_type'] = 'ol'; //this is for formatting later.
			$this->mFilteredExifData['ComponentsConfiguration'] = $ccVals;
		}
	
		//GPSVersion(ID) is treated as the wrong type by php exif support.
		//Go through each byte turning it into a version string.
		//For example: "\x02\x02\x00\x00" -> "2.2.0.0"

		//Also change exif tag name from GPSVersion (what php exif thinks it is)
		//to GPSVersionID (what the exif standard thinks it is).

		if ( isset ( $this->mFilteredExifData['GPSVersion'] ) ) {
			$val = $this->mFilteredExifData['GPSVersion'];
			$newVal = '';
			for ($i = 0; $i < strlen($val); $i++) {
				if ( $i !== 0 ) {
					$newVal .= '.';
				}
				$newVal .= ord( substr($val, $i, 1) );
			}
			$this->mFilteredExifData['GPSVersionID'] = $newVal;
			unset( $this->mFilteredExifData['GPSVersion'] );
		}

	}
	/**
	* Do userComment tags and similar. See pg. 34 of exif standard.
	* basically first 8 bytes is charset, rest is value.
	* This has not been tested on any shift-JIS strings.
	* @param $prop String prop name.
	*/
	private function charCodeString ( $prop ) {
		if ( isset( $this->mFilteredExifData[$prop] ) ) {

			if ( strlen($this->mFilteredExifData[$prop]) <= 8 ) {
				//invalid. Must be at least 9 bytes long.

				$this->debug( $this->mFilteredExifData[$prop] , __FUNCTION__, false );
				unset($this->mFilteredExifData[$prop]);
				return;
			}

			$charCode = substr( $this->mFilteredExifData[$prop], 0, 8);
			$val = substr( $this->mFilteredExifData[$prop], 8);
			
			
			switch ($charCode) {
				case "\x4A\x49\x53\x00\x00\x00\x00\x00":
					//JIS
					$charset = "Shift-JIS";
					break;
				case "UNICODE\x00":
					$charset = "UTF-16";
					break;
				default: //ascii or undefined.
					$charset = "";
					break;
			}
			// This could possibly check to see if iconv is really installed
			// or if we're using the compatability wraper in globalFunctions.php
			if ($charset) {
				$val = iconv($charset, 'UTF-8//IGNORE', $val);
			} else {
				// if valid utf-8, assume that, otherwise assume windows-1252
				$valCopy = $val;
				UtfNormal::quickIsNFCVerify( $valCopy ); //validates $valCopy.
				if ( $valCopy !== $val ) {
					$val = iconv('Windows-1252', 'UTF-8//IGNORE', $val);
				}
			}
			
			//trim and check to make sure not only whitespace.
			$val = trim($val);
			if ( strlen( $val ) === 0 ) {
				//only whitespace.
				$this->debug( $this->mFilteredExifData[$prop] , __FUNCTION__, "$prop: Is only whitespace" );
				unset($this->mFilteredExifData[$prop]);
				return;
			}

			//all's good.
			$this->mFilteredExifData[$prop] = $val;
		}
	}
	/**
	* Convert an Exif::UNDEFINED from a raw binary string
	* to its value. This is sometimes needed depending on
	* the type of UNDEFINED field
	* @param $prop String name of property
	*/
	private function exifPropToOrd ( $prop ) {
		if ( isset( $this->mFilteredExifData[$prop] ) ) {
			$this->mFilteredExifData[$prop] = ord( $this->mFilteredExifData[$prop] );
		}
	}
	/**
	* Convert gps in exif form to a single floating point number
	* for example 10 degress 20`40`` S -> -10.34444
	* @param String $prop a gps coordinate exif tag name (like GPSLongitude)
	*/
	private function exifGPStoNumber ( $prop ) {
		$loc =& $this->mFilteredExifData[$prop];
		$dir =& $this->mFilteredExifData[$prop . 'Ref'];
		$res = false;

		if ( isset( $loc ) && isset( $dir ) && ( $dir === 'N' || $dir === 'S' || $dir === 'E' || $dir === 'W' ) ) {
			list( $num, $denom ) = explode( '/', $loc[0] );
			$res = $num / $denom;
			list( $num, $denom ) = explode( '/', $loc[1] );
			$res += ( $num / $denom ) * ( 1 / 60 );
			list( $num, $denom ) = explode( '/', $loc[2] );
			$res += ( $num / $denom ) * ( 1 / 3600 );

			if ( $dir === 'S' || $dir === 'W' ) {
				$res *= - 1; // make negative
			}
		}

		// update the exif records.

		if ( $res !== false ) { // using !== as $res could potentially be 0
			$this->mFilteredExifData[$prop] = $res;
			unset( $this->mFilteredExifData[$prop . 'Ref'] );
		} else { // if invalid
			unset( $this->mFilteredExifData[$prop] );
			unset( $this->mFilteredExifData[$prop . 'Ref'] );
		}
	}

	/**
	 * Use FormatExif to create formatted values for display to user
	 * (is this ever used?)
	 */
	private function makeFormattedData( ) {
		$format = new FormatExif( $this->getFilteredData() );
		$this->mFormattedExifData = $format->getFormattedData();
	}
	/**#@-*/

	/**#@+
	 * @return array
	 */
	/**
	 * Get $this->mRawExifData
	 */
	function getData() {
		return $this->mRawExifData;
	}

	/**
	 * Get $this->mFilteredExifData
	 */
	function getFilteredData() {
		return $this->mFilteredExifData;
	}

	/**
	 * Get $this->mFormattedExifData
	 */
	function getFormattedData() {
		if (!$this->mFormattedExifData) {
			$this->makeFormattedData();
		}
		return $this->mFormattedExifData;
	}
	/**#@-*/

	/**
	 * The version of the output format
	 *
	 * Before the actual metadata information is saved in the database we
	 * strip some of it since we don't want to save things like thumbnails
	 * which usually accompany Exif data. This value gets saved in the
	 * database along with the actual Exif data, and if the version in the
	 * database doesn't equal the value returned by this function the Exif
	 * data is regenerated.
	 *
	 * @return int
	 */
	public static function version() {
		return 2; // We don't need no bloddy constants!
	}

	/**#@+
	 * Validates if a tag value is of the type it should be according to the Exif spec
	 *
	 * @private
	 *
	 * @param $in Mixed: the input value to check
	 * @return bool
	 */
	private function isByte( $in ) {
		if ( !is_array( $in ) && sprintf('%d', $in) == $in && $in >= 0 && $in <= 255 ) {
			$this->debug( $in, __FUNCTION__, true );
			return true;
		} else {
			$this->debug( $in, __FUNCTION__, false );
			return false;
		}
	}

	private function isASCII( $in ) {
		if ( is_array( $in ) ) {
			return false;
		}

		if ( preg_match( "/[^\x0a\x20-\x7e]/", $in ) ) {
			$this->debug( $in, __FUNCTION__, 'found a character not in our whitelist' );
			return false;
		}

		if ( preg_match( '/^\s*$/', $in ) ) {
			$this->debug( $in, __FUNCTION__, 'input consisted solely of whitespace' );
			return false;
		}

		return true;
	}

	private function isShort( $in ) {
		if ( !is_array( $in ) && sprintf('%d', $in) == $in && $in >= 0 && $in <= 65536 ) {
			$this->debug( $in, __FUNCTION__, true );
			return true;
		} else {
			$this->debug( $in, __FUNCTION__, false );
			return false;
		}
	}

	private function isLong( $in ) {
		if ( !is_array( $in ) && sprintf('%d', $in) == $in && $in >= 0 && $in <= 4294967296 ) {
			$this->debug( $in, __FUNCTION__, true );
			return true;
		} else {
			$this->debug( $in, __FUNCTION__, false );
			return false;
		}
	}

	private function isRational( $in ) {
		$m = array();
		if ( !is_array( $in ) && @preg_match( '/^(\d+)\/(\d+[1-9]|[1-9]\d*)$/', $in, $m ) ) { # Avoid division by zero
			return $this->isLong( $m[1] ) && $this->isLong( $m[2] );
		} else {
			$this->debug( $in, __FUNCTION__, 'fed a non-fraction value' );
			return false;
		}
	}

	private function isUndefined( $in ) {

		$this->debug( $in, __FUNCTION__, true );
		return true;

		/* Exif::UNDEFINED means string of bytes
		so this validation does not make sense.
		comment out for now.
		if ( !is_array( $in ) && preg_match( '/^\d{4}$/', $in ) ) { // Allow ExifVersion and FlashpixVersion
			$this->debug( $in, __FUNCTION__, true );
			return true;
		} else {
			$this->debug( $in, __FUNCTION__, false );
			return false;
		}
		*/
	}

	private function isSlong( $in ) {
		if ( $this->isLong( abs( $in ) ) ) {
			$this->debug( $in, __FUNCTION__, true );
			return true;
		} else {
			$this->debug( $in, __FUNCTION__, false );
			return false;
		}
	}

	private function isSrational( $in ) {
		$m = array();
		if ( !is_array( $in ) && preg_match( '/^(-?\d+)\/(\d+[1-9]|[1-9]\d*)$/', $in, $m ) ) { # Avoid division by zero
			return $this->isSlong( $m[0] ) && $this->isSlong( $m[1] );
		} else {
			$this->debug( $in, __FUNCTION__, 'fed a non-fraction value' );
			return false;
		}
	}
	/**#@-*/

	/**
	 * Validates if a tag has a legal value according to the Exif spec
	 *
	 * @private
	 * @param $section String: section where tag is located.
	 * @param $tag String: the tag to check.
	 * @param $val Mixed: the value of the tag.
	 * @param $recursive Boolean: true if called recursively for array types.
	 * @return bool
	 */
	private function validate( $section, $tag, $val, $recursive = false ) {
		$debug = "tag is '$tag'";
		$etype = $this->mExifTags[$section][$tag];
		$ecount = 1;
		if( is_array( $etype ) ) {
			list( $etype, $ecount ) = $etype;
			if ( $recursive )
				$ecount = 1; // checking individual elements
		}
		$count = count( $val );
		if( $ecount != $count ) {
			$this->debug( $val, __FUNCTION__, "Expected $ecount elements for $tag but got $count" );
			return false;
		}
		if( $count > 1 ) {
			foreach( $val as $v ) { 
				if( !$this->validate( $section, $tag, $v, true ) ) {
					return false; 
				} 
			}
			return true;
		}
		// Does not work if not typecast
		switch( (string)$etype ) {
			case (string)Exif::BYTE:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isByte( $val );
			case (string)Exif::ASCII:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isASCII( $val );
			case (string)Exif::SHORT:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isShort( $val );
			case (string)Exif::LONG:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isLong( $val );
			case (string)Exif::RATIONAL:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isRational( $val );
			case (string)Exif::UNDEFINED:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isUndefined( $val );
			case (string)Exif::SLONG:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isSlong( $val );
			case (string)Exif::SRATIONAL:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isSrational( $val );
			case (string)Exif::SHORT.','.Exif::LONG:
				$this->debug( $val, __FUNCTION__, $debug );
				return $this->isShort( $val ) || $this->isLong( $val );
			case (string)Exif::IGNORE:
				$this->debug( $val, __FUNCTION__, $debug );
				return false;
			default:
				$this->debug( $val, __FUNCTION__, "The tag '$tag' is unknown" );
				return false;
		}
	}

	/**
	 * Convenience function for debugging output
	 *
	 * @private
	 *
	 * @param $in Mixed:
	 * @param $fname String:
	 * @param $action Mixed: , default NULL.
	 */
	private function debug( $in, $fname, $action = null ) {
		if ( !$this->log ) {
			return;
		}
		$type = gettype( $in );
		$class = ucfirst( __CLASS__ );
		if ( $type === 'array' )
			$in = print_r( $in, true );

		if ( $action === true )
			wfDebugLog( $this->log, "$class::$fname: accepted: '$in' (type: $type)\n");
		elseif ( $action === false )
			wfDebugLog( $this->log, "$class::$fname: rejected: '$in' (type: $type)\n");
		elseif ( $action === null )
			wfDebugLog( $this->log, "$class::$fname: input was: '$in' (type: $type)\n");
		else
			wfDebugLog( $this->log, "$class::$fname: $action (type: $type; content: '$in')\n");
	}

	/**
	 * Convenience function for debugging output
	 *
	 * @private
	 *
	 * @param $fname String: the name of the function calling this function
	 * @param $io Boolean: Specify whether we're beginning or ending
	 */
	private function debugFile( $fname, $io ) {
		if ( !$this->log ) {
			return;
		}
		$class = ucfirst( __CLASS__ );
		if ( $io ) {
			wfDebugLog( $this->log, "$class::$fname: begin processing: '{$this->basename}'\n" );
		} else {
			wfDebugLog( $this->log, "$class::$fname: end processing: '{$this->basename}'\n" );
		}
	}

}

/**
 * Format Image metadata values into a human readable form.
 * Note despite the name, this formats more than just exif
 * values.
 * @todo Perhaps rename to FormatMetadata
 * @ingroup Media
 */
class FormatExif {
	/**
	 * The Exif data to format. Note, this now also formats non-exif data
	 *
	 * @var array
	 * @private
	 */
	var $mExif;

	/**
	 * Constructor
	 *
	 * @param $exif Array: the Exif data to format ( as returned by
	 *                    Exif::getFilteredData() )
	 */
	function FormatExif( $exif ) {
		$this->mExif = $exif;
	}

	/**
	 * Numbers given by Exif user agents are often magical, that is they
	 * should be replaced by a detailed explanation depending on their
	 * value which most of the time are plain integers. This function
	 * formats Exif values into human readable form.
	 *
	 * @return array
	 */
	function getFormattedData() {
		global $wgLang;

		$tags =& $this->mExif;

		$resolutionunit = !isset( $tags['ResolutionUnit'] ) || $tags['ResolutionUnit'] == 2 ? 2 : 3;
		unset( $tags['ResolutionUnit'] );

		foreach ( $tags as $tag => &$vals ) {

			// This seems ugly to wrap non-array's in an array just to unwrap again,
			// especially when most of the time it is not an array
			if ( !is_array( $tags[$tag] ) ) {
				$vals = Array( $vals );
			}

			// _type is a special value to say what array type
			if ( isset( $tags[$tag]['_type'] ) ) {
				$type = $tags[$tag]['_type'];
				unset( $vals['_type'] );
			} else {
				$type = 'ul'; // default unorcdered list.
			}

			//This is done differently as the tag is an array.
			if ($tag == 'GPSTimeStamp' && count($vals) === 3) {
				//hour min sec array

				$h = explode('/', $vals[0]);
				$m = explode('/', $vals[1]);
				$s = explode('/', $vals[2]);

				// this should already be validated
				// when loaded from file, but it could
				// come from a foreign repo, so be
				// paranoid.
				if ( !isset($h[1])
					|| !isset($m[1])
					|| !isset($s[1])
					|| $h[1] == 0
					|| $m[1] == 0
					|| $s[1] == 0
				) {
					continue;
				}
				$tags[$tag] = intval( $h[0] / $h[1] )
					. ':' . intval( $m[0] / $m[1] )
					. ':' . str_pad( intval( $s[0] / $s[1] ), 2, '0', STR_PAD_LEFT );
				continue;
			}

		
			foreach ( $vals as &$val ) {

				switch( $tag ) {
				case 'Compression':
					switch( $val ) {
					case 1: case 6:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'PhotometricInterpretation':
					switch( $val ) {
					case 2: case 6:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'Orientation':
					switch( $val ) {
					case 1: case 2: case 3: case 4: case 5: case 6: case 7: case 8:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'PlanarConfiguration':
					switch( $val ) {
					case 1: case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				// TODO: YCbCrSubSampling
				case 'YCbCrPositioning':
					switch ( $val ) {
					case 1:
					case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'XResolution':
				case 'YResolution':
					switch( $resolutionunit ) {
						case 2:
							$val = $this->msg( 'XYResolution', 'i', $this->formatNum( $val ) );
							break;
						case 3:
							$this->msg( 'XYResolution', 'c', $this->formatNum( $val ) );
							break;
						default:
							$val = $val;
							break;
					}
					break;

				// TODO: YCbCrCoefficients  #p27 (see annex E)
				case 'ExifVersion': case 'FlashpixVersion':
					$val = "$val" / 100;
					break;

				case 'ColorSpace':
					switch( $val ) {
					case 1: case 'FFFF.H':
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'ComponentsConfiguration':
					switch( $val ) {
					case 0: case 1: case 2: case 3: case 4: case 5: case 6:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'DateTime':
				case 'DateTimeOriginal':
				case 'DateTimeDigitized':
				case 'DateTimeReleased':
				case 'DateTimeExpires':
				case 'GPSDateStamp':
				case 'dc-date':
					if ( $val == '0000:00:00 00:00:00' || $val == '    :  :     :  :  ' ) {
						$val = wfMsg( 'exif-unknowndate' );
					} elseif ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d) (?:\d\d):(?:\d\d):(?:\d\d)$/', $val ) ) {
						$val = $wgLang->timeanddate( wfTimestamp( TS_MW, $val ) );
					} elseif ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d)$/', $val ) ) {
						// avoid using wfTimestamp here for the pre-1902 photos
						// due to reverse y2k38 bug. $wgLang->timeanddate() is also
						// broken on dates from before 1902 so don't worry about it
						// in the above case (not to mention that most photos from the
						// 1800's don't have a time recorded anyways).
						$val = $wgLang->date( substr( $val, 0, 4 )
							. substr( $val, 5, 2 )
							. substr( $val, 8, 2 )
							. '000000' );
					}
					// else it will just output $val without formatting it.
					break;

				case 'ExposureProgram':
					switch( $val ) {
					case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7: case 8:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'SubjectDistance':
					$val = $this->msg( $tag, '', $this->formatNum( $val ) );
					break;

				case 'MeteringMode':
					switch( $val ) {
					case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7: case 255:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'LightSource':
					switch( $val ) {
					case 0: case 1: case 2: case 3: case 4: case 9: case 10: case 11:
					case 12: case 13: case 14: case 15: case 17: case 18: case 19: case 20:
					case 21: case 22: case 23: case 24: case 255:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'Flash':
					$flashDecode = array(
						'fired'    => $val & bindec( '00000001' ),
						'return'   => ( $val & bindec( '00000110' ) ) >> 1,
						'mode'     => ( $val & bindec( '00011000' ) ) >> 3,
						'function' => ( $val & bindec( '00100000' ) ) >> 5,
						'redeye'   => ( $val & bindec( '01000000' ) ) >> 6,
//						'reserved' => ($val & bindec( '10000000' )) >> 7,
					);
	
					# We do not need to handle unknown values since all are used.
					foreach ( $flashDecode as $subTag => $subValue ) {
						# We do not need any message for zeroed values.
						if ( $subTag != 'fired' && $subValue == 0 ) {
							continue;
						}
						$fullTag = $tag . '-' . $subTag ;
						$flashMsgs[] = $this->msg( $fullTag, $subValue );
					}
					$val = $wgLang->commaList( $flashMsgs );
					break;

				case 'FocalPlaneResolutionUnit':
					switch( $val ) {
					case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'SensingMethod':
					switch( $val ) {
					case 1: case 2: case 3: case 4: case 5: case 7: case 8:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'FileSource':
					switch( $val ) {
					case 3:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'SceneType':
					switch( $val ) {
					case 1:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'CustomRendered':
					switch( $val ) {
					case 0: case 1:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'ExposureMode':
					switch( $val ) {
					case 0: case 1: case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'WhiteBalance':
					switch( $val ) {
					case 0: case 1:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'SceneCaptureType':
					switch( $val ) {
					case 0: case 1: case 2: case 3:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GainControl':
					switch( $val ) {
					case 0: case 1: case 2: case 3: case 4:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'Contrast':
					switch( $val ) {
					case 0: case 1: case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'Saturation':
					switch( $val ) {
					case 0: case 1: case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'Sharpness':
					switch( $val ) {
					case 0: case 1: case 2:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'SubjectDistanceRange':
					switch( $val ) {
					case 0: case 1: case 2: case 3:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				//The GPS...Ref values are kept for compatability, probably won't be reached.
				case 'GPSLatitudeRef':
				case 'GPSDestLatitudeRef':
					switch( $val ) {
					case 'N': case 'S':
						$val = $this->msg( 'GPSLatitude', $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSLongitudeRef':
				case 'GPSDestLongitudeRef':
					switch( $val ) {
					case 'E': case 'W':
						$val = $this->msg( 'GPSLongitude', $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSAltitude':
					if ( $val < 0 ) {
						$val = $this->msg( 'GPSAltitude', 'below-sealevel', $this->formatNum( -$val ) );
					} else {
						$val = $this->msg( 'GPSAltitude', 'above-sealevel', $this->formatNum( $val ) );
					}
					break;

				case 'GPSStatus':
					switch( $val ) {
					case 'A': case 'V':
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSMeasureMode':
					switch( $val ) {
					case 2: case 3:
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;


				case 'GPSTrackRef':
				case 'GPSImgDirectionRef':
				case 'GPSDestBearingRef':
					switch( $val ) {
					case 'T': case 'M':
						$val = $this->msg( 'GPSDirection', $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSLatitude':
				case 'GPSDestLatitude':
					$val = $this->formatCoords( $val, 'latitude' );
					break;
				case 'GPSLongitude':
				case 'GPSDestLongitude':
					$val = $this->formatCoords( $val, 'longitude' );
					break;

				case 'GPSSpeedRef':
					switch( $val ) {
					case 'K': case 'M': case 'N':
						$val = $this->msg( 'GPSSpeed', $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSDestDistanceRef':
					switch( $val ) {
					case 'K': case 'M': case 'N':
						$val = $this->msg( 'GPSDestDistance', $val );
						break;
					default:
						$val = $val;
						break;
					}
					break;

				case 'GPSDOP':
					// See http://en.wikipedia.org/wiki/Dilution_of_precision_(GPS)
					if ( $val <= 2 ) {
						$val = $this->msg( $tag, 'excellent', $this->formatNum( $val ) );
					} elseif ( $val <= 5 ) {
						$val = $this->msg( $tag, 'good', $this->formatNum( $val ) );
					} elseif ( $val <= 10 ) {
						$val = $this->msg( $tag, 'moderate', $this->formatNum( $val ) );
					} elseif ( $val <= 20 ) {
						$val = $this->msg( $tag, 'fair', $this->formatNum( $val ) );
					} else {
						$val = $this->msg( $tag, 'poor', $this->formatNum( $val ) );
					}
					break;

	

				// This is not in the Exif standard, just a special
				// case for our purposes which enables wikis to wikify
				// the make, model and software name to link to their articles.
				case 'Make':
				case 'Model':
					$val = $this->msg( $tag, '', $val );
					break;

				case 'Software':
					if ( is_array( $val ) ) {
						//if its a software, version array.
						$val = wfMsg( 'exif-software-version-value', $val[0], $val[1] );
					} else {
						$val = $this->msg( $tag, '', $val );
					}
					break;

				case 'ExposureTime':
					// Show the pretty fraction as well as decimal version
					$val = wfMsg( 'exif-exposuretime-format',
						$this->formatFraction( $val ), $this->formatNum( $val ) );
					break;

				case 'FNumber':
					$val = wfMsg( 'exif-fnumber-format',
						$this->formatNum( $val ) );
					break;

				case 'FocalLength':
					$val = wfMsg( 'exif-focallength-format',
						$this->formatNum( $val ) );
					break;

				// Do not transform fields with pure text.
				// For some languages the formatNum() conversion results to wrong output like
				// foo,bar@example,com or foo٫bar@example٫com
				case 'ImageDescription':
				case 'Artist':
				case 'Copyright':
				case 'GPSVersionID':
				case 'Keywords':
				case 'CountryDest':
				case 'CountryDestCode':
				case 'ProvinceOrStateDest':
				case 'CityDest':
				case 'SublocationDest':
				case 'ObjectName':
				case 'SpecialInstructions':
				case 'Headline':
				case 'Credit':
				case 'Source':
				case 'EditStatus':
				case 'Urgency':
				case 'FixtureIdentifier':
				case 'LocationDest':
				case 'LocationDestCode':
				case 'Contact':
				case 'Writer':
				case 'JPEGFileComment':
				case 'iimCategory':
				case 'iimSupplementalCategory':
				case 'OriginalTransmissionRef':
				case 'Identifier':
				case 'dc-contributor':
				case 'dc-coverage':
				case 'dc-publisher':
				case 'dc-relation':
				case 'dc-rights':
				case 'dc-source':
				case 'dc-type':

					$val = htmlspecialchars( $val );
					break;

				case 'ObjectCycle':
					switch ( $val ) {
					case 'a': case 'p': case 'b':
						$val = $this->msg( $tag, $val );
						break;
					default:
						$val = htmlspecialchars( $val );
						break;
					}
					break;

				case 'LanguageCode':
					$lang = $wgLang->getLanguageName( strtolower( $val ) );
					if ($lang) {
						$val = htmlspecialchars( $lang );
					} else {
						$val = htmlspecialchars( $val );
					}
					break;

				default:
					$val = $this->formatNum( $val );
					break;
				}
			}
			// End formatting values, start flattening arrays.
			$vals = $this->flattenArray( $vals, $type );

		}
		return $this->mExif;
	}

	/**
	* A function to collapse multivalued tags into a single value.
	* This turns an array of (for example) authors into a bulleted list.
	* This might be used outside of this class.
	* @public 
	* 
	* @param $vals Array array of values
	* @param $type Type of array (either lang, ul, ol).
	* lang = language assoc array with keys being the lang code
	* ul = unorded list, ol = ordered list
	* type can also come from the '_type' member of $vals.
	* @return String single value (in wiki-syntax).
	*/
	public static function flattenArray( $vals, $type = 'ul' ) {

		if ( isset( $vals['_type'] ) ) {
			$type = $vals['_type'];
		}

		if ( !is_array( $vals ) ) {
			 return $vals; // do nothing if not an array;
		}
		elseif ( count( $vals ) === 1 && $type !== 'lang' ) {
			return $vals[0];
		}
		elseif ( count( $vals ) === 0 ) {
			return ""; // paranoia. This should never happen
			wfDebug( __METHOD__ . ' metadata array with 0 elements!' );
		}
		/* Fixme: This should hide some of the list entries if there are
		* say more than four. Especially if a field is translated into 20
		* languages, we don't want to show them all by default
		*/
		else {
			switch( $type ) {
			case 'lang':
				// fixme incomplete
				// should place x-default, content language, user language
				// first. then the others, hidden by defualt.
				// also should use much better markup.
				$content = "";
				if ( $vals['x-default'] ) {
					$content .= "\n*" . $vals['x-default'];
					unset( $vals['x-default'] );
				}
				foreach ( $vals as $lang => $item ) {
					global $wgContLang;
					$content .= "\n*<span lang=\"$lang\">"
						. "'''$lang''' $item</span>";
				}
				return $content;
			case 'ol':
				return "<ol><li>" . implode( "</li>\n<li>", $vals ) . '</li></ol>';
			case 'ul':
			default:
				return "<ul><li>" . implode( "</li>\n<li>", $vals ) . '</li></ul>';
			}
		}
	}
	/**
	 * Convenience function for getFormattedData()
	 *
	 * @private
	 *
	 * @param $tag String: the tag name to pass on
	 * @param $val String: the value of the tag
	 * @param $arg String: an argument to pass ($1)
	 * @return string A wfMsg of "exif-$tag-$val" in lower case
	 */
	function msg( $tag, $val, $arg = null ) {
		global $wgContLang;

		if ($val === '')
			$val = 'value';
		return wfMsg( $wgContLang->lc( "exif-$tag-$val" ), $arg );
	}

	/**
	 * Format a number, convert numbers from fractions into floating point
	 * numbers, joins arrays of numbers with commas.
	 *
	 * @private
	 *
	 * @param $num Mixed: the value to format
	 * @return mixed A floating point number or whatever we were fed
	 */
	function formatNum( $num ) {
		global $wgLang;
		$m = array();
		if( is_array($num) ) {
			$out = array();
			foreach( $num as $number ) {
				$out[] = $this->formatNum($number);
			}
			return $wgLang->commaList( $out );
		}
		if ( preg_match( '/^(-?\d+)\/(\d+)$/', $num, $m ) )
			return $wgLang->formatNum( $m[2] != 0 ? $m[1] / $m[2] : $num );
		else
			return $wgLang->formatNum( $num );
	}

	/**
	 * Format a rational number, reducing fractions
	 *
	 * @private
	 *
	 * @param $num Mixed: the value to format
	 * @return mixed A floating point number or whatever we were fed
	 */
	function formatFraction( $num ) {
		$m = array();
		if ( preg_match( '/^(-?\d+)\/(\d+)$/', $num, $m ) ) {
			$numerator = intval( $m[1] );
			$denominator = intval( $m[2] );
			$gcd = $this->gcd( abs( $numerator ), $denominator );
			if( $gcd != 0 ) {
				// 0 shouldn't happen! ;)
				return $this->formatNum( $numerator / $gcd ) . '/' . $this->formatNum( $denominator / $gcd );
			}
		}
		return $this->formatNum( $num );
	}

	/**
	 * Calculate the greatest common divisor of two integers.
	 *
	 * @param $a Integer: Numerator
	 * @param $b Integer: Denominator
	 * @return int
	 * @private
	 */
	function gcd( $a, $b ) {
		/*
			// http://en.wikipedia.org/wiki/Euclidean_algorithm
			// Recursive form would be:
			if( $b == 0 )
				return $a;
			else
				return gcd( $b, $a % $b );
		*/
		while( $b != 0 ) {
			$remainder = $a % $b;

			// tail recursion...
			$a = $b;
			$b = $remainder;
		}
		return $a;
	}

	/**
	 * Format a coordinate value, convert numbers from fractions
	 * into floating point numbers, .
	 *
	 * @private
	 *
	 * @param $coords Array: degrees, minutes and seconds
	 * @param $type String: latitude or longitude (for if its a NWS or E)
	 * @return mixed A floating point number or whatever we were fed
	 */
	function formatCoords( $coord, $type ) {
		$ref = '';
		if ( $coord < 0 ) {
			$nCoord = -$coord;
			if ( $type === 'latitude' ) {
				$ref = 'S';
			}
			elseif ( $type === 'longitude' ) {
				$ref = 'W';
			}
		}
		else {
			$nCoord = $coord;
			if ( $type === 'latitude' ) {
				$ref = 'N';
			}
			elseif ( $type === 'longitude' ) {
				$ref = 'E';
			}
		}

		$deg = floor( $nCoord );
		$min = floor( ( $nCoord - $deg ) * 60.0 );
		$sec = round( ( ( $nCoord - $deg ) - $min / 60 ) * 3600, 2 );

		$deg = $this->formatNum( $deg );
		$min = $this->formatNum( $min );
		$sec = $this->formatNum( $sec );

		return wfMsg( 'exif-coordinate-format', $deg, $min, $sec, $ref, $coord );
	}

}
