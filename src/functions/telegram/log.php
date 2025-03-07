<?php

function log_message($incoming_message): bool
{
	$data = json_decode($incoming_message, true);

	if ($data === null) {
		return false;
	}

	$json_data = json_encode(array("data" => json_encode($data)));

	if ($json_data === false) {
		return false;
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://api.us-east.aws.tinybird.co/v0/events?name=telegram_updates",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $json_data,
		CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer " . $_ENV["TOKEN"],
			"Content-Type: application/json"
		),
	));

	curl_exec($curl);
	return true;
}
