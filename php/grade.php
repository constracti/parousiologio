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

	public static function select_options(): array {
		$grades = grade::select();
		$options = [];
		foreach ( $grades as $grade )
			$options[ $grade->grade_id ] = $grade->grade_name;
		return $options;
	}
}
