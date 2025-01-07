<?php

/**
 * Controls attached to core sections
 *
 * @package vamtam/innovecouture
 */


return array(
	array(
		'label'     => esc_html__( 'Header Logo Type', 'innovecouture' ),
		'id'        => 'header-logo-type',
		'type'      => 'switch',
		'transport' => 'postMessage',
		'section'   => 'title_tagline',
		'choices'   => array(
			'image'      => esc_html__( 'Image', 'innovecouture' ),
			'site-title' => esc_html__( 'Site Title', 'innovecouture' ),
		),
		'priority' => 8,
	),

	array(
		'label'     => esc_html__( 'Single Product Image Zoom', 'innovecouture' ),
		'id'        => 'wc-product-gallery-zoom',
		'type'      => 'switch',
		'transport' => 'postMessage',
		'section'   => 'woocommerce_product_images',
		'choices'   => array(
			'enabled'  => esc_html__( 'Enabled', 'innovecouture' ),
			'disabled' => esc_html__( 'Disabled', 'innovecouture' ),
		),
		// 'active_callback' => 'vamtam_extra_features',
	),
);


