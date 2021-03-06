<?php
/**
 * Implements Special:Booksources
 *
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
 * @file
 * @ingroup SpecialPage
 */

/**
 * Special page outputs information on sourcing a book with a particular ISBN
 * The parser creates links to this page when dealing with ISBNs in wikitext
 *
 * @author Rob Church <robchur@gmail.com>
 * @todo Validate ISBNs using the standard check-digit method
 * @ingroup SpecialPage
 */
class SpecialBookSources extends SpecialPage {

	/**
	 * ISBN passed to the page, if any
	 */
	private $isbn = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'Booksources' );
	}

	/**
	 * Show the special page
	 *
	 * @param $isbn ISBN passed as a subpage parameter
	 */
	public function execute( $isbn ) {
		global $wgOut, $wgRequest;
		$this->setHeaders();
		$this->isbn = self::cleanIsbn( $isbn ? $isbn : $wgRequest->getText( 'isbn' ) );
		$wgOut->addWikiMsg( 'booksources-summary' );
		$wgOut->addHTML( $this->makeForm() );
		if( strlen( $this->isbn ) > 0 ) {
			if( !self::isValidISBN( $this->isbn ) ) {
				$wgOut->wrapWikiMsg( "<div class=\"error\">\n$1\n</div>", 'booksources-invalid-isbn' );
			}
			$this->showList();
		}
	}

	/**
	 * Returns whether a given ISBN (10 or 13) is valid.  True indicates validity.
	 * @param isbn ISBN passed for check
	 */
	public static function isValidISBN( $isbn ) {
		$isbn = self::cleanIsbn( $isbn );
		$sum = 0;
		if( strlen( $isbn ) == 13 ) {
			for( $i = 0; $i < 12; $i++ ) {
				if($i % 2 == 0) {
					$sum += $isbn[$i];
				} else {
					$sum += 3 * $isbn[$i];
				}
			}

			$check = (10 - ($sum % 10)) % 10;
			if ($check == $isbn[12]) {
				return true;
			}
		} elseif( strlen( $isbn ) == 10 ) {
			for($i = 0; $i < 9; $i++) {
				$sum += $isbn[$i] * ($i + 1);
			}

			$check = $sum % 11;
			if($check == 10) {
				$check = "X";
			}
			if($check == $isbn[9]) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Trim ISBN and remove characters which aren't required
	 *
	 * @param $isbn Unclean ISBN
	 * @return string
	 */
	private static function cleanIsbn( $isbn ) {
		return trim( preg_replace( '![^0-9X]!', '', $isbn ) );
	}

	/**
	 * Generate a form to allow users to enter an ISBN
	 *
	 * @return string
	 */
	private function makeForm() {
		global $wgScript;
		$title = self::getTitleFor( 'Booksources' );
		$form  = '<fieldset><legend>' . wfMsgHtml( 'booksources-search-legend' ) . '</legend>';
		$form .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= Html::hidden( 'title', $title->getPrefixedText() );
		$form .= '<p>' . Xml::inputLabel( wfMsg( 'booksources-isbn' ), 'isbn', 'isbn', 20, $this->isbn );
		$form .= '&#160;' . Xml::submitButton( wfMsg( 'booksources-go' ) ) . '</p>';
		$form .= Xml::closeElement( 'form' );
		$form .= '</fieldset>';
		return $form;
	}

	/**
	 * Determine where to get the list of book sources from,
	 * format and output them
	 *
	 * @return string
	 */
	private function showList() {
		global $wgOut, $wgContLang;

		# Hook to allow extensions to insert additional HTML,
		# e.g. for API-interacting plugins and so on
		wfRunHooks( 'BookInformation', array( $this->isbn, &$wgOut ) );

		# Check for a local page such as Project:Book_sources and use that if available
		$title = Title::makeTitleSafe( NS_PROJECT, wfMsgForContent( 'booksources' ) ); # Show list in content language
		if( is_object( $title ) && $title->exists() ) {
			$rev = Revision::newFromTitle( $title );
			$wgOut->addWikiText( str_replace( 'MAGICNUMBER', $this->isbn, $rev->getText() ) );
			return true;
		}

		# Fall back to the defaults given in the language file
		$wgOut->addWikiMsg( 'booksources-text' );
		$wgOut->addHTML( '<ul>' );
		$items = $wgContLang->getBookstoreList();
		foreach( $items as $label => $url )
			$wgOut->addHTML( $this->makeListItem( $label, $url ) );
		$wgOut->addHTML( '</ul>' );
		return true;
	}

	/**
	 * Format a book source list item
	 *
	 * @param $label Book source label
	 * @param $url Book source URL
	 * @return string
	 */
	private function makeListItem( $label, $url ) {
		$url = str_replace( '$1', $this->isbn, $url );
		return '<li><a href="' . htmlspecialchars( $url ) . '" class="external">' . htmlspecialchars( $label ) . '</a></li>';
	}
}
