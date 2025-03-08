<?php

require_once __DIR__ . '/vendor/autoload.php'; 
require_once __DIR__ . '/strings.php';
require_once __DIR__ . '/helpers.php';

use SergiX44\Nutgram\Nutgram;

function handler($event = null, $context = null)
{
	$tinybird = new TinybirdClient(
		token: $_ENV['TOKEN'],
		baseUrl: 'https://api.europe-west3.gcp.tinybird.co/v0');
	$telegram = new Nutgram($_ENV['API_KEY']);

	/* ------------------- Validate and parse the request body ------------------ */

	if (!isset($event['body'])) exit(json_encode([
		'level' => 'FATAL',
		'message' => 'Parameter `body` is missing in the request',
		'context' => ['requester_ip' => $event['requestContext']['identity']['sourceIp']]
	]));

	parse_str(
		string: base64_decode(
			string: $event['body'],
			strict: true
		),
		result: $params
	);
	$model = $params['model'] ?? null;

	if (!isset($model) || $model !== 'discountcards') {
		exit(json_encode([
			'level' => 'FATAL',
			'message' => 'Parameter `model` from the `body` is not `discountcards`',
			'context' => ['model' => $model]
		]));
	}

	/* ------------------- Extract and process the bonus sums ------------------- */

	$old_state = json_decode($params['changes']['0']['data']);
	$new_state = json_decode($params['data']);

	$old_sum = $old_state['bonus_sum'];
	$new_sum = $new_state['bonus_sum'];
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
			params: ['phone_number' => $new_state->{'num'}]
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
