<?php

class grade extends entity {

	const FIELDS = [
		'grade_id'    => 'i',
		'category_id' => 'i',
		'grade_name'  => 's',
	];

	public $grade_id;    # integer, primary key
	public $category_id; # integer
	public $grade_name;  # varchar
}