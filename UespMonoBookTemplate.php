<?php
/**
 * MonoBook nouveau.
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
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
 * @ingroup Skins
 */

/**
 * @ingroup Skins
 */
require_once "skins/MonoBook/includes/MonoBookTemplate.php";

class UespMonoBookTemplate extends MonoBookTemplate {
	function execute() {
		$sn = $this->get('sitenotice');
		$this->set('sitenotice', "<div id='topad'><div class='center' id='uespTopBannerAd' style='margin:0 auto;'><div id='uesp_D_1'></div></div></div>$sn");
		$dac = $this->get('dataAfterContent');
		// Somewhat of a fugly hack to close divs we need to be outside of, but then re-open empty ones so as not to create incorrect code.
		$this->set('dataAfterContent', "$dac<div class='visualClear'></div></div></div><div style='width:300px; height:250px; margin:0 auto;'><div id='uesp_D_3'></div></div><div><div>");
		parent::execute();
	}
	
	function printTrail() {
		parent::printTrail();
	}

	function getRenderedSidebar() {
		$sidebar = $this->data['sidebar'];
		$html = '';

		if ( !isset( $sidebar['SEARCH'] ) ) {
			$sidebar['SEARCH'] = true;
		}
		if ( !isset( $sidebar['TOOLBOX'] ) ) {
			$sidebar['TOOLBOX'] = true;
		}
		if ( !isset( $sidebar['LANGUAGES'] ) ) {
			$sidebar['LANGUAGES'] = true;
		}

		foreach ( $sidebar as $boxName => $content ) {
			if ( $content === false ) {
				continue;
			}

			// Numeric strings gets an integer when set as key, cast back - T73639
			$boxName = (string)$boxName;

			if ( $boxName == 'SEARCH' ) {
				$html .= $this->getSearchBox();
			} elseif ( $boxName == 'TOOLBOX' ) {
				$html .= $this->getToolboxBox();
			} elseif ( $boxName == 'LANGUAGES' ) {
				$html .= $this->getLanguageBox();
			} else {
				//die(var_export($sidebar,true));
				$html .= $this->getBox(
					$boxName,
					$content,
					null,
					[ 'extra-classes' => 'generated-sidebar' ]
				);
			}
		}

		$html .= Html::openElement('div', ['class' => 'portlet']);
		$html .= Html::rawElement('div', ['id' => 'uesp_D_2']);
		$html .= Html::closeElement('div');

		return $html;
	}

	function getSearchBox() {
		$html = '';
		
		$html .= Html::openElement( 'div', [ 'id' => 'p-search', 'class' => 'portlet', 'role' => 'search' ]);
		$html .= Html::rawElement( 'h3' , [], Html::rawElement( 'label' , [ 'for' => 'searchInput' ], $this->getMsg( 'search' )->parse() )); 
		$html .= Html::openElement( 'div', [ 'id' => 'searchBody', 'class' => 'pBody', 'style' => 'background-color: white; height: 12px;'] );
		$html .= Html::rawElement( 'form', [ 'action' => $this->get( 'wgScript' ), 'id' => 'searchform' ], 
			Html::hidden( 'title', $this->get( 'searchtitle' ) ) .
			Html::element( 'input', [ 'title' => 'Search UESPWiki [f]', 'accesskey' => 'f', 'name' => 'search', 'id' => 'searchInput', 'style' => '-webkit-appearance: none; background-color: transparent; width: 90%; margin: 0; font-size: 13px; border: medium none; outline: medium none; direction: ltr; left: 1px; margin: 0; padding: .2em 0 .2em .2em; position: absolute; top: 18px; height: 16px;' ]) .
			Html::rawElement( 'button', [ 'id' => 'searchGoButton', 'title' => 'Search UESP for this text', 'name' => 'button', 'type' => 'submit', 'style' => 'background-color: transparent; background-image: none; border: medium none; cursor: pointer; margin: 0; padding: .2em .4em .2em 0; position: absolute; right: 0; top: 18px; width: 10%;' ] ,
				Html::element( 'img', [ 'width' => 12, 'height' => 13, 'alt' => 'Search', 'src' => '/w/skins/UespMonoBook/search-icon.png'])
			)
		);
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );
		
		return $html;
	}

	protected function getBox( $name, $contents, $msg = null, $setOptions = [] ) {
		$options = [
			'class' => 'portlet',
			'body-class' => 'pBody',
			'text-wrapper' => ''
		];
		foreach ( $setOptions as $key => $value ) {
			$options[$key] = $value;
		}

		// Do some special stuff for the personal menu
		if ( $name == 'personal' ) {
			$prependiture = '';

			// Extension:UniversalLanguageSelector order - T121793
			if ( array_key_exists( 'uls', $contents ) ) {
				$prependiture .= $this->makeListItem( 'uls', $contents['uls'] );
				unset( $contents['uls'] );
			}
			if ( !$this->getSkin()->getUser()->isLoggedIn() &&
				User::groupHasPermission( '*', 'edit' )
			) {
				$prependiture .= Html::rawElement(
					'li',
					[ 'id' => 'pt-anonuserpage' ],
					$this->getMsg( 'notloggedin' )->escaped()
				);
			}
			$options['list-prepend'] = $prependiture;
		}
		$portlet = $this->getPortlet( $name, $contents, $msg, $options );
		
		return $portlet;
	}

	protected function getPortlet( $name, $content, $msg = null, $setOptions = [] ) {
		// random stuff to override with any provided options
		$options = [
			// handle role=search a little differently
			'role' => 'navigation',
			'search-input-id' => 'searchInput',
			// extra classes/ids
			'id' => 'p-' . $name,
			'class' => 'mw-portlet',
			'extra-classes' => '',
			'body-id' => null,
			'body-class' => 'mw-portlet-body',
			'body-extra-classes' => '',
			// wrapper for individual list items
			'text-wrapper' => [ 'tag' => 'span' ],
			// old toolbox hook support (use: [ 'SkinTemplateToolboxEnd' => [ &$skin, true ] ])
			'hooks' => '',
			// option to stick arbitrary stuff at the beginning of the ul
			'list-prepend' => ''
		];
		// set options based on input
		foreach ( $setOptions as $key => $value ) {
			$options[$key] = $value;
		}

		// Handle the different $msg possibilities
		if ( $msg === null ) {
			$msg = $name;
			$msgParams = [];
		} elseif ( is_array( $msg ) ) {
			$msgString = array_shift( $msg );
			$msgParams = $msg;
			$msg = $msgString;
		} else {
			$msgParams = [];
		}
		$msgObj = $this->getMsg( $msg, $msgParams );
		if ( $msgObj->exists() ) {
			$msgString = $msgObj->parse();
		} else {
			$msgString = htmlspecialchars( $msg );
		}

		$labelId = Sanitizer::escapeIdForAttribute( "p-$name-label" );

		if ( is_array( $content ) ) {
			$contentText = Html::openElement( 'ul',
				[ 'lang' => $this->get( 'userlang' ), 'dir' => $this->get( 'dir' ) ]
			);
			$contentText .= $options['list-prepend'];
			$isindent = false;
			foreach ( $content as $key => $item ) {
				if (substr($item['text'],0,1)=='*') {
					if (!$isindent)
						$contentText .= Html::openElement('ul');
					$isindent = true;
					$item['text'] = trim(substr($item['text'],1));
					$item['id'] = str_replace('.2A', '', $item['id']);
				}
				elseif ($isindent) {
					$isindent = false;
					$contentText .= Html::closeElement('ul');
				}
				if ( is_array( $options['text-wrapper'] ) ) {
					$contentText .= $this->makeListItem(
						$key,
						$item,
						[ 'text-wrapper' => $options['text-wrapper'] ]
					);
				} else {
					$contentText .= $this->makeListItem(
						$key,
						$item
					);
				}
			}
			// Compatibility with extensions still using SkinTemplateToolboxEnd or similar
			if ( is_array( $options['hooks'] ) ) {
				foreach ( $options['hooks'] as $hook => $hookOptions ) {
					$contentText .= $this->deprecatedHookHack( $hook, $hookOptions );
				}
			}

			$contentText .= Html::closeElement( 'ul' );
		} else {
			$contentText = $content;
		}

		// Special handling for role=search
		$divOptions = [
			'role' => $options['role'],
			'class' => $this->mergeClasses( $options['class'], $options['extra-classes'] ),
			'id' => Sanitizer::escapeIdForAttribute( $options['id'] ),
			'title' => Linker::titleAttrib( $options['id'] )
		];
		if ( $options['role'] !== 'search' ) {
			$divOptions['aria-labelledby'] = $labelId;
		}
		$labelOptions = [
			'id' => $labelId,
			'lang' => $this->get( 'userlang' ),
			'dir' => $this->get( 'dir' )
		];
		if ( $options['role'] == 'search' ) {
			$msgString = Html::rawElement( 'label', [ 'for' => $options['search-input-id'] ], $msgString );
		}

		$bodyDivOptions = [
			'class' => $this->mergeClasses( $options['body-class'], $options['body-extra-classes'] )
		];
		if ( is_string( $options['body-id'] ) ) {
			$bodyDivOptions['id'] = $options['body-id'];
		}

		$html = Html::rawElement( 'div', $divOptions,
			Html::rawElement( 'h3', $labelOptions, $msgString ) .
			Html::rawElement( 'div', $bodyDivOptions,
				$contentText .
				$this->getAfterPortlet( $name )
			)
		);

		return $html;
	}
} // end of class
