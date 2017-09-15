<?php

# TODO rename `name` to `description`
# TODO rename `date` to `event_date`

class event extends entity {

	const FIELDS = [
		'event_id'  => 'i',
		'name'      => 's',
		'date'      => 's',
		'season_id' => 'i',
	];

	public $event_id;  # integer, primary key
	public $name;      # varchar, nullable
	public $date;      # date
	public $season_id; # integer
}