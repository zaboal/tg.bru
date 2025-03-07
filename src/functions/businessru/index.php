<?php

function sendMessage($chat, $text)
{
	$data = [
		'chat_id' => $chat,
		'text' => $text
	];
	$api = $_ENV['API_KEY'];
	$url = "https://api.telegram.org/bot$api/sendMessage";
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_exec($ch);
	curl_close($ch);
}

function getChat($number)
{
	$url = 'https://api.us-east.aws.tinybird.co/v0/pipes/telegram_contacts.json';
	$params = ['q' => "SELECT id FROM _ WHERE phone = '" . $number . "'"];
	$token = $_ENV['TOKEN'];

	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $url . '?' . http_build_query($params),
		CURLOPT_HTTPHEADER => [
			'Authorization: Bearer ' . $token,
			'Accept-Encoding: gzip'
		],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 30
	]);

	$response = curl_exec($ch);
	$response = json_decode(gzdecode($response));
	curl_close($ch);

	if (! $response->data[0]) {
		return false;
	}
	
	return (int)$response->data[0]->phone;
}

function handler($event, $context)
{
	$text = require __DIR__ . '/texts.php';

	if (!isset($event['body'])) {
		exit("Missing required 'body' parameter in request");
	}

	$body = base64_decode($event['body'], true);
	parse_str($body, $params);

	if (!isset($params['model']) || $params['model'] !== 'discountcards') {
		exit("Parameter 'model' must be 'discountcards'");
	}
	
	$changes = json_decode($params['changes']);
	$data = json_decode($params['data']);

	$old_sum = $changes->{'0'}->{'data'}->{'bonus_sum'};
	$new_sum = $data->{'bonus_sum'};
	$delta_sum = $new_sum - $old_sum;

	if ($delta_sum < 0) {
		$text = sprintf($text['decrease'], abs($delta_sum), $new_sum);
	} elseif ($delta_sum > 0) {
		$text = sprintf($text['increase'], abs($delta_sum), $new_sum);
	} else {
		exit("No changes in bonus sum");
	}

	$id = getChat($data->{'num'});
	sendMessage($id, $text);

	return [
		'statusCode' => 200,
		'body' => json_encode([
			'status' => 'success',
			'message' => 'Notification sent successfully'
		])
	];
}
