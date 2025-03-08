<?php

require_once __DIR__ . '/vendor/autoload.php'; 
require_once __DIR__ . '/helpers.php';

use SergiX44\Nutgram\Nutgram;

function handler($event = null, $context = null)
{
	$strings = require_once __DIR__ . '/strings.php';

	$tinybird = new TinybirdClient(
		token: $_ENV['TOKEN'],
		baseUrl: 'https://api.europe-west3.gcp.tinybird.co/v0');
	$telegram = new Nutgram($_ENV['API_KEY']);

	/* ------------------- Validate and parse the request body ------------------ */

	print(json_encode([
		'level' => 'DEBUG',
		'message' => 'Received a new event',
		'context' => [
			'event' => $event
	]]) . PHP_EOL);

	if (!isset($event['body'])) exit(json_encode([
		'level' => 'FATAL',
		'message' => 'Parameter `body` is missing in the request',
		'context' => ['requester_ip' => $event['requestContext']['identity']['sourceIp']]
	]));

	parse_str(base64_decode($event['body']), $params);
	
	print(json_encode([
		'level' => 'DEBUG',
		'message' => 'Parsed and Base64-decoded the request parameters in `body`',
		'context' => [
			'params' => $params
	]]) . PHP_EOL);
	
	$model = $params['model'] ?? null;
	if (!isset($model) || $model !== 'discountcards') {
		exit(json_encode([
			'level' => 'FATAL',
			'message' => 'Parameter `model` from the `body` is not `discountcards`',
			'context' => ['model' => $model]
		]));
	}

	/* ------------------- Extract and process the bonus sums ------------------- */

	$param_changes = json_decode($params['changes']);
	$param_data = json_decode($params['data']);

	print(json_encode([
		'level' => 'DEBUG',
		'message' => 'Decoded JSONs at `changes` and `data` parameters',
		'context' => [
			'changes' => $param_changes,
			'data' => $param_data
	]]) . PHP_EOL);

	$old_state = $param_changes->{0}->data;
	$new_state = $param_data;

	$old_sum = $old_state->bonus_sum;
	$new_sum = $new_state->bonus_sum;
	$sum_change = $new_sum - $old_sum;

	if ($sum_change == 0) exit(json_encode([
		'level' => 'FATAL',
		'message' => 'The `bonus_sum` has not changed',
		'context' => ['old_sum' => $old_sum, 'new_sum' => $new_sum]
	]));

	/* --------------------- Send a notification to the user -------------------- */

	$message = $telegram->sendMessage(
		chat_id: $tinybird->query(
			pipe: 'contacts_api',
			params: ['phone_number' => $new_state->num]
		)['data']['0']['telegram_id'],
		text: sprintf(
			format: $strings[$sum_change < 0 ? 'decrease' : 'increase'],
			values: [abs($sum_change), $new_sum]
		)
	);

	return [
		'statusCode' => $message !== null ? 200 : 500,
		'body' => json_encode([
			'status' => $message !== null ? 'success' : 'error',
			'message' => $message !== null ? 'Notification sent successfully' : 'Failed to send notification'
		])
	];
}
