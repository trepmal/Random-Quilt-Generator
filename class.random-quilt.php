<?php
/**
 * Random Quilt
 */
class Random_Quilt {

	/**
	 *
	 */
	var $salt;

	/**
	 *
	 */
	var $grid_size = 5;

	/**
	 *
	 */
	var $block_size = 50;

	/**
	 *
	 */
	function __construct( $salt = 'NaCl' ) {
		$this->salt = $this->numify( $salt );
	}

	/**
	 * Set grid size
	 *
	 * @param int $int An integer
	 */
	function set_grid_size( $int ) {
		$int = $int > 100 ? 100 : $int;
		$this->grid_size = abs( intval( $int ) );
	}

	/**
	 * Set block size
	 *
	 * @param int $int An integer
	 */
	function set_block_size( $int ) {
		$this->block_size = abs( intval( $int ) );
	}

	/**
	 * Turn string into an integer
	 *
	 * @param string $string Any string
	 * @return int
	 */
	private function numify( $string ) {
		return hexdec( substr( sha1( $string ), 0, 8 ) );
	}

	/**
	 * Get colors
	 *
	 * @return array Hex color codes
	 */
	private function get_colors() {
		$bits = $this->init_hash();

		$bits = $this->shuffle_array( $bits );

		$bits = array_chunk( $bits, 3 );

		return $bits;
	}

	/**
	 * Using salt, get hexdec components
	 *
	 * 1. Figure out how many squares there will be,
	 * 2. For each square, need 6 characters for the color
	 * 3. Generate enough hash to accommodate
	 * 4. Split into 2 character chunks
	 *
	 * @return array Twenty 2-character hexdec strings
	 */
	private function init_hash() {

		$area   = pow( $this->grid_size, 2 );
		$needed = $area*6; // 6 = length of color hex code
		$repeat = ceil( $needed/40 ); // 40 = length of sha1 output

		$hash   = sha1( $this->salt );
		$hash   = str_repeat( $hash, $repeat );

		$hash = str_split(
			substr( $hash, 0, $needed )
		, 2 );

		return $hash;
	}

	/**
	 * Shuffles an array in a repeatable manner
	 *
	 * @link http://stackoverflow.com/a/19658344
	 * @param array $items The array to be shuffled.
	 * @return array Shuffled items
	 */
	function shuffle_array( $items ) {
		$items = array_values( $items );
		mt_srand( $this->salt );
		for ( $i = count( $items ) - 1; $i > 0; $i-- ) {
			$j = mt_rand( 0, $i );
			list( $items[ $i ], $items[ $j ] ) = array( $items[ $j ], $items[ $i ] );
		}
		return $items;
	}

	/**
	 * Build image
	 *
	 * @param string|null $path Path to save file to
	 * @param bool $base64 Whether to out a base64 encoded image or not
	 */
	function build_image( $path=null, $base64=false ) {

		$colors = $this->get_colors();

		// make a 10x10 pattern
		$grid_size = $this->grid_size;

		// size of each "pixel"
		$unit = $this->block_size;

		// start making a square image
		$im = imagecreatetruecolor( $grid_size * $unit, $grid_size * $unit );

		// width/height position trackers
		$wpos = $hpos = 0;

		foreach ( $colors as $rgb ) {

			list( $r, $g, $b ) = array_map( 'hexdec', $rgb );
			$block_color = imagecolorallocate( $im, $r, $g, $b );

			imagefilledrectangle( $im, $wpos, $hpos, $wpos+$unit, $hpos+$unit, $block_color );
			$hpos += $unit;

			// new col
			if ( $hpos == $grid_size*$unit ) {
				$wpos += $unit;
				$hpos = 0;
			}

		}

		if ( $base64 ) {
			ob_start();
		} else {
			header( 'Content-Type: image/png' );
			header( 'Content-Disposition: filename="identicon.png"' );
		}
		imagepng( $im, $path );
		imagedestroy( $im );
		if ( $base64 ) {
			header( 'Content-Type: text/plain' );
			die( base64_encode( ob_get_clean() ) );
		}
	}

}
