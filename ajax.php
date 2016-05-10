<?php 
	require_once "config.php";

	class AjaxCall
	{
		function __construct()
		{
			$r = $_GET;
			if(!isset($r['action']))
				die(0);

			@call_user_func([$this,$r['action']]);
		}

		function getPlacesList()
		{
			header('Content-Type: application/json');
			$places = [];

			$query = QUERY_SEARCH_LIST;
			$result = Neo::$client->run($query);
			$records = $result->records();

			foreach ($records as $record)
			{
				$places[] = [
				"names" => $record->value("names")
				, "id" => $record->value("id")
				];
			}
			$places = json_encode($places);
			print_r($places);
		}
	}
	new AjaxCall();
?>
