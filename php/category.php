<?php

class category extends entity {

	const FIELDS = [
		'category_id'   => 'i',
		'category_name' => 's',
	];

	public $category_id;   # integer, primary key
	public $category_name; # varchar
}
