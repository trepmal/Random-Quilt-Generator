<?php
/**
 * Example.
 */

require_once( 'class.random-quilts.php' );

// Use salt mmmmmm
$salt = isset( $_GET['salt'] ) ? $_GET['salt'] : 'NaCl';

$quilt = new Random_Quilts( $salt );
// $quilt->set_grid_size( 20 );
// $quilt->set_block_size( 30 );
$quilt->build_image();

