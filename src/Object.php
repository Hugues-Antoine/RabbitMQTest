<?php 

namespace RabbitmqTest;

class Object
{
	function __construct($id)
	{
		sleep(1);
	}

	public function genCacheMembers()
	{
		$time = rand(0, 5);
		$nb = 0;
		while ($nb <= $time) {
			$nb++;
			echo '.';
			sleep(1);
		}
	}
}
