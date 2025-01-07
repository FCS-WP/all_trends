<?php
return array(
	'name' => esc_html__( 'Help', 'innovecouture' ),
	'auto' => true,
	'config' => array(

		array(
			'name' => esc_html__( 'Help', 'innovecouture' ),
			'type' => 'title',
			'desc' => '',
		),

		array(
			'name' => esc_html__( 'Help', 'innovecouture' ),
			'type' => 'start',
			'nosave' => true,
		),
//----
		array(
			'type' => 'docs',
		),

			array(
				'type' => 'end',
			),
	),
);
