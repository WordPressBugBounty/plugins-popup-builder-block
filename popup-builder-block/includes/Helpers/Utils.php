<?php

namespace PopupBuilderBlock\Helpers;

defined( 'ABSPATH' ) || exit;

use PopupBuilderBlock\Config\SettingsList;

/**
 * Global helper class.
 *
 * @since 1.0.0
 */

class Utils {

	/**
	 * Returns an array of allowed HTML tags and attributes for SVG elements.
	 *
	 * @return array The array of allowed HTML tags and attributes.
	 */
	public static function svg_allowed_html() {
		$allowed_svg_tags = array(
			'svg'            => array(
				'xmlns'               => true,
				'width'               => true,
				'height'              => true,
				'viewBox'             => true,
				'viewbox'             => true,
				'fill'                => true,
				'class'               => true,
				'aria-hidden'         => true,
				'aria-labelledby'     => true,
				'role'                => true,
				'preserveaspectratio' => true,
				'version'             => true,
			),
			'title'          => array( 'title' => true ),
			'g'              => array(
				'transform' => true,
				'style'     => true,
				'id'        => true,
			),
			'path'           => array(
				'd'                 => true,
				'fill'              => true,
				'fill-rule'         => true,
				'transform'         => true,
				'style'             => true,
				'opacity'           => true,
				'stroke'            => true,
				'stroke-width'      => true,
				'stroke-miterlimit' => true,
				'stroke-linecap'    => true,
				'stroke-linejoin'   => true,
				'fill-opacity'      => true,
			),
			'circle'         => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'ellipse'        => array(
				'cx'           => true,
				'cy'           => true,
				'rx'           => true,
				'ry'           => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'line'           => array(
				'x1'           => true,
				'y1'           => true,
				'x2'           => true,
				'y2'           => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'polygon'        => array(
				'points'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'polyline'       => array(
				'points'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'rect'           => array(
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'text'           => array(
				'x'           => true,
				'y'           => true,
				'dx'          => true,
				'dy'          => true,
				'text-anchor' => true,
				'style'       => true,
			),
			'tspan'          => array(
				'x'           => true,
				'y'           => true,
				'dx'          => true,
				'dy'          => true,
				'text-anchor' => true,
				'style'       => true,
			),
			'defs'           => array(),
			'lineargradient' => array(
				'id'            => true,
				'x1'            => true,
				'y1'            => true,
				'x2'            => true,
				'y2'            => true,
				'gradientunits' => true,
			),
			'stop'           => array(
				'offset'       => true,
				'style'        => true,
				'stop-color'   => true,
				'stop-opacity' => true,
			),
			'radialgradient' => array(
				'id'                => true,
				'cx'                => true,
				'cy'                => true,
				'r'                 => true,
				'gradientunits'     => true,
				'gradienttransform' => true,
			),
		);

		return apply_filters( 'popup_builder_block/allowed_svg_attrs_tags', $allowed_svg_tags );
	}

	/**
	 * Returns an array of allowed JSON attribute tags.
	 *
	 * This function defines an array of allowed JSON attribute tags and their corresponding properties.
	 * The array includes tags such as 'object', 'array', 'string', 'number', 'integer', 'boolean', 'null',
	 * 'enum', 'const', 'oneOf', 'allOf', 'anyOf', 'not', 'if', 'then', 'else', and 'format'.
	 *
	 * @return array The array of allowed JSON attribute tags.
	 */
	public static function allowed_json_attrs_tags() {
		$allowed_json_tags = array(
			'object'      => array(
				'type'                 => true,
				'properties'           => true,
				'required'             => true,
				'additionalProperties' => true,
				'propertyNames'        => true,
				'dependencies'         => true,
				'minProperties'        => true,
				'maxProperties'        => true,
			),
			'array'       => array(
				'type'        => true,
				'items'       => true,
				'minItems'    => true,
				'maxItems'    => true,
				'uniqueItems' => true,
			),
			'string'      => array(
				'type'             => true,
				'minLength'        => true,
				'maxLength'        => true,
				'pattern'          => true,
				'format'           => true,
				'contentEncoding'  => true,
				'contentMediaType' => true,
			),
			'number'      => array(
				'type'             => true,
				'minimum'          => true,
				'maximum'          => true,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => true,
				'multipleOf'       => true,
			),
			'integer'     => array(
				'type'             => true,
				'minimum'          => true,
				'maximum'          => true,
				'exclusiveMinimum' => true,
				'exclusiveMaximum' => true,
			),
			'boolean'     => array(
				'type' => true,
			),
			'null'        => array(
				'type' => true,
			),
			'enum'        => array(
				'type' => true,
				'enum' => true,
			),
			'const'       => array(
				'type'  => true,
				'const' => true,
			),
			'oneOf'       => array(
				'type'  => true,
				'oneOf' => true,
			),
			'allOf'       => array(
				'type'  => true,
				'allOf' => true,
			),
			'anyOf'       => array(
				'type'  => true,
				'anyOf' => true,
			),
			'not'         => array(
				'type' => true,
				'not'  => true,
			),
			'if'          => array(
				'type' => true,
				'if'   => true,
			),
			'then'        => array(
				'type' => true,
				'then' => true,
			),
			'else'        => array(
				'type' => true,
				'else' => true,
			),
			'format'      => array(
				'type'   => true,
				'format' => true,
			),
			'title'       => true,
			'description' => true,
			'default'     => true,
			'examples'    => true,
			'$ref'        => true,
			'$id'         => true,
			'$schema'     => true,
			// Adding Lottie-specific keys
			'v'           => true,
			'meta'        => true,
			'fr'          => true,
			'ip'          => true,
			'op'          => true,
			'w'           => true,
			'h'           => true,
			'nm'          => true,
			'ddd'         => true,
			'assets'      => true,
			'layers'      => true,
			'markers'     => true,
		);

		return apply_filters( 'popup_builder_block/allowed_json_attrs_tags', $allowed_json_tags );
	}

	private static function check_plugin_status( $plugin_path ) {
		$validate_plugin = validate_plugin( $plugin_path );
		if(is_wp_error( $validate_plugin )) {
			return 'notInstalled';
		}

		return is_plugin_active( $plugin_path ) ? 'active' : 'inactive';
	}

	public static function onboard_plugins() {
		return array(
			'rox-dynamic-cpt-fields-engine' => self::check_plugin_status( 'rox-dynamic-cpt-fields-engine/rox-dynamic-cpt-fields-engine.php' ),
			'rox-appointment-booking'       => self::check_plugin_status( 'rox-appointment-booking/rox-appointment-booking.php' ),
			'gutenkit-blocks-addon'         => self::check_plugin_status( 'gutenkit-blocks-addon/gutenkit-blocks-addon.php' ),
			'emailkit'                      => self::check_plugin_status( 'emailkit/EmailKit.php' ),
			'elementskit-lite'              => self::check_plugin_status( 'elementskit-lite/elementskit-lite.php' ),
			'metform'                       => self::check_plugin_status( 'metform/metform.php' ),
			'getgenie'                      => self::check_plugin_status( 'getgenie/getgenie.php' ),
			'blocks-for-shopengine'         => self::check_plugin_status( 'blocks-for-shopengine/shopengine-gutenberg-addon.php' ),
		);
	}

	/**
	 * Check if the block is a Popup Builder block.
	 *
	 * @param string $attrs
	 * @return bool
	 */
	public static function is_popup_block( $block_content, $parsed_block, $attrs = '', $attrs2 = '' ) {
		// Check if $block_content is not empty
		$hasBlockContent = ! empty( $block_content );

		// Check if $block['blockName'] is not empty and contains 'popup-builder-block'
		$hasValidBlockName = ! empty( $parsed_block['blockName'] ) && strpos( $parsed_block['blockName'], 'popup-builder-block' ) !== false;

		// Check if $block['attrs']['blockClass'] is not empty
		$hasBlockClass = ! empty( $attrs ) && ! empty( $attrs2 )
			? ! empty( $parsed_block['attrs'][ $attrs ][ $attrs2 ] )
			: ! empty( $parsed_block['attrs'][ $attrs ] ?? '' );

		if ( empty( $attrs ) && empty( $attrs2 ) ) {
			$hasBlockClass = true;
		}

		// Return true if all conditions are met
		return $hasBlockContent && $hasValidBlockName && $hasBlockClass;
	}

	/**
	 * Retrieves the settings from the specified key in the options table.
	 *
	 * @param string $key The key of the settings in the options table.
	 * @param string $list Optional. The specific list within the settings to retrieve.
	 * @param string $option Optional. The specific option within the list to retrieve.
	 * @return mixed The retrieved settings, or false if the list or option is not found.
	 */
	public static function get_settings( $key = '', $field = 'status', $inner_field = '' ) {
		$settings = get_option( 'pbb-settings-tabs' );

		if ( empty( $settings ) ) {
			$settings = SettingsList::pbb_settings_list();
		}

		// check if $key & $field both empty
		if ( empty( $key ) && empty( $field ) ) {
			return $settings;
		}

		// check for primary key
		if ( ! empty( $key ) && ! empty( $settings[ $key ] ) ) {
			$settings = $settings[ $key ];
		}

		// check for primary field
		if ( ! empty( $field ) ) {
			if ( $field === 'status' && ! empty( $settings[ $field ] ) ) {
				return ( $settings[ $field ] === 'active' ) ? true : false;
			} else {
				$settings = ! empty( $settings[ $field ] ) ? $settings[ $field ] : false;
			}
		}

		// check for inner field
		if ( ! empty( $inner_field ) ) {
			if ( isset( $settings[ $inner_field ]['value'] ) ) {
				$settings = $settings[ $inner_field ]['value'];
			} else {
				$settings = ! empty( $settings[ $inner_field ] ) ? $settings[ $inner_field ] : false;
			}
		}

		return $settings;
	}

	// TODO: Remove this function after 2.3.12 or later version
	public static function status() {

		$cached = wp_cache_get( 'pbb__license_status' );

		if ( false !== $cached ) {
			return $cached;
		}

		$oppai  = get_option( '__pbb_oppai__', '' );
		$key    = get_option( '__pbb_license_key__', '' );
		$status = 'invalid';

		if ( $oppai != '' && $key != '' ) {
			$status = 'valid';
		}

		wp_cache_set( 'pbb__license_status', $status );

		return $status;
	}

	// TODO: Remove this function after 2.3.12 or later version
	public static function is_local() {
		$valid_domains = array(
			'.academy',
			'.accountant',
			'.accountants',
			'.actor',
			'.adult',
			'.africa',
			'.agency',
			'.airforce',
			'.apartments',
			'.app',
			'.army',
			'.art',
			'.asia',
			'.associates',
			'.attorney',
			'.auction',
			'.audio',
			'.auto',
			'.baby',
			'.band',
			'.bar',
			'.bargains',
			'.beer',
			'.berlin',
			'.best',
			'.bid',
			'.bike',
			'.bingo',
			'.bio',
			'.biz',
			'.black',
			'.blackfriday',
			'.blog',
			'.blue',
			'.boston',
			'.boutique',
			'.build',
			'.builders',
			'.business',
			'.buzz',
			'.cab',
			'.cafe',
			'.cam',
			'.camera',
			'.camp',
			'.capital',
			'.car',
			'.cards',
			'.care',
			'.careers',
			'.cars',
			'.casa',
			'.cash',
			'.casino',
			'.catering',
			'.center',
			'.ceo',
			'.chat',
			'.cheap',
			'.christmas',
			'.church',
			'.city',
			'.claims',
			'.cleaning',
			'.click',
			'.clinic',
			'.clothing',
			'.cloud',
			'.club',
			'.coach',
			'.codes',
			'.coffee',
			'.college',
			'.com',
			'.community',
			'.company',
			'.computer',
			'.condos',
			'.construction',
			'.consulting',
			'.contact',
			'.contractors',
			'.cooking',
			'.cool',
			'.country',
			'.coupons',
			'.courses',
			'.credit',
			'.creditcard',
			'.cricket',
			'.cruises',
			'.cymru',
			'.cyou',
			'.dance',
			'.date',
			'.dating',
			'.day',
			'.deals',
			'.degree',
			'.delivery',
			'.democrat',
			'.dental',
			'.dentist',
			'.desi',
			'.design',
			'.dev',
			'.diamonds',
			'.diet',
			'.digital',
			'.direct',
			'.directory',
			'.discount',
			'.doctor',
			'.dog',
			'.domains',
			'.download',
			'.earth',
			'.eco',
			'.education',
			'.email',
			'.energy',
			'.engineer',
			'.engineering',
			'.enterprises',
			'.equipment',
			'.estate',
			'.events',
			'.exchange',
			'.expert',
			'.exposed',
			'.express',
			'.fail',
			'.faith',
			'.family',
			'.fans',
			'.farm',
			'.fashion',
			'.feedback',
			'.film',
			'.finance',
			'.financial',
			'.fish',
			'.fishing',
			'.fit',
			'.fitness',
			'.flights',
			'.florist',
			'.flowers',
			'.football',
			'.forsale',
			'.foundation',
			'.fun',
			'.fund',
			'.furniture',
			'.futbol',
			'.fyi',
			'.gallery',
			'.game',
			'.games',
			'.garden',
			'.gay',
			'.gdn',
			'.gift',
			'.gifts',
			'.gives',
			'.glass',
			'.global',
			'.gmbh',
			'.gold',
			'.golf',
			'.graphics',
			'.gratis',
			'.green',
			'.gripe',
			'.group',
			'.guide',
			'.guitars',
			'.guru',
			'.hamburg',
			'.haus',
			'.health',
			'.healthcare',
			'.help',
			'.hiphop',
			'.hockey',
			'.holdings',
			'.holiday',
			'.horse',
			'.host',
			'.hosting',
			'.house',
			'.how',
			'.icu',
			'.immo',
			'.immobilien',
			'.inc',
			'.industries',
			'.info',
			'.ink',
			'.institute',
			'.insure',
			'.international',
			'.investments',
			'.irish',
			'.jetzt',
			'.jewelry',
			'.juegos',
			'.kaufen',
			'.kim',
			'.kitchen',
			'.kiwi',
			'.krd',
			'.kyoto',
			'.land',
			'.lat',
			'.lawyer',
			'.lease',
			'.legal',
			'.lgbt',
			'.life',
			'.lighting',
			'.limited',
			'.limo',
			'.link',
			'.live',
			'.llc',
			'.loan',
			'.loans',
			'.lol',
			'.london',
			'.love',
			'.ltd',
			'.ltda',
			'.luxury',
			'.maison',
			'.management',
			'.market',
			'.marketing',
			'.mba',
			'.media',
			'.melbourne',
			'.memorial',
			'.men',
			'.menu',
			'.miami',
			'.mobi',
			'.moda',
			'.moe',
			'.mom',
			'.money',
			'.monster',
			'.mortgage',
			'.movie',
			'.nagoya',
			'.name',
			'.navy',
			'.net',
			'.network',
			'.new',
			'.news',
			'.ninja',
			'.nyc',
			'.observer',
			'.okinawa',
			'.one',
			'.onl',
			'.online',
			'.org',
			'.osaka',
			'.page',
			'.paris',
			'.partners',
			'.parts',
			'.party',
			'.photo',
			'.photography',
			'.photos',
			'.pics',
			'.pictures',
			'.pink',
			'.pizza',
			'.place',
			'.plumbing',
			'.plus',
			'.poker',
			'.porn',
			'.press',
			'.pro',
			'.productions',
			'.properties',
			'.property',
			'.protection',
			'.pub',
			'.racing',
			'.realty',
			'.recipes',
			'.red',
			'.rehab',
			'.reise',
			'.reisen',
			'.rent',
			'.rentals',
			'.repair',
			'.report',
			'.republican',
			'.rest',
			'.restaurant',
			'.review',
			'.reviews',
			'.rip',
			'.rocks',
			'.rodeo',
			'.run',
			'.ryukyu',
			'.sale',
			'.sarl',
			'.school',
			'.schule',
			'.science',
			'.security',
			'.services',
			'.sex',
			'.sexy',
			'.shiksha',
			'.shoes',
			'.shop',
			'.shopping',
			'.show',
			'.singles',
			'.site',
			'.ski',
			'.soccer',
			'.social',
			'.software',
			'.solar',
			'.solutions',
			'.soy',
			'.space',
			'.storage',
			'.store',
			'.stream',
			'.studio',
			'.study',
			'.style',
			'.sucks',
			'.supplies',
			'.supply',
			'.support',
			'.surf',
			'.surgery',
			'.sydney',
			'.systems',
			'.tattoo',
			'.tax',
			'.taxi',
			'.team',
			'.tech',
			'.technology',
			'.tel',
			'.tennis',
			'.theater',
			'.theatre',
			'.tienda',
			'.tips',
			'.tires',
			'.today',
			'.tokyo',
			'.tools',
			'.top',
			'.tours',
			'.town',
			'.toys',
			'.trade',
			'.training',
			'.travel',
			'.tube',
			'.university',
			'.uno',
			'.vacations',
			'.vegas',
			'.ventures',
			'.vet',
			'.viajes',
			'.video',
			'.villas',
			'.vin',
			'.vip',
			'.vision',
			'.vodka',
			'.vote',
			'.voting',
			'.voto',
			'.voyage',
			'.wales',
			'.watch',
			'.webcam',
			'.website',
			'.wedding',
			'.wiki',
			'.win',
			'.wine',
			'.work',
			'.works',
			'.world',
			'.wtf',
			'.xn--3ds443g',
			'.xn--6frz82g',
			'.xxx',
			'.xyz',
			'.yoga',
			'.yokohama',
			'.zone',
		);

		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		// get the domain
		$domain = explode( '.', $host );
		if ( count( $domain ) >= 2 ) {
			$domain = '.' . $domain[ count( $domain ) - 1 ];
		} else {
			$domain = null;
		}

		return ! in_array( $domain, $valid_domains );
	}

	public static function post_type() {
		$post_type = array(
			'popupkit-campaigns',
		);
		$result    = apply_filters( 'popup_builder_block/allow_post_type', $post_type );
		return $result;
	}

	public static function is_preview() {
		return filter_input( INPUT_GET, 'preview', FILTER_VALIDATE_BOOLEAN );
	}

	public static function is_iframe() {
		return filter_input( INPUT_GET, 'iframe', FILTER_VALIDATE_BOOLEAN );
	}
}
