<?php

require_once __DIR__ . '/vendor/autoload.php'; 
require_once __DIR__ . '/helpers.php';

use SergiX44\Nutgram\Nutgram;

function handler($event = null, $context = null)
{
	$strings = require_once __DIR__ . '/strings.php';

	$logger = new YandexFunctionsLogger(__CLASS__);
	$tinybird = new TinybirdClient($_ENV['TOKEN']);
	$telegram = new Nutgram($_ENV['API_KEY']);

	/* ------------------- Validate and parse the request body ------------------ */

	$logger->debug('Received a new event', ['event' => $event]);

	if (!isset($event['body'])) exit(json_encode([
		'level' => 'FATAL',
		'message' => 'Parameter `body` is missing in the request',
		'context' => ['requester_ip' => $event['requestContext']['identity']['sourceIp']]
	]));

	parse_str(base64_decode($event['body']), $params);
	
	$logger->debug('Decoded and parsed and the parameters inside the body', ['params' => $params]);
	
	$params['model'] ?? $logger->fatal('Parameter `model` from the `body` is not `discountcards`', ['model' => $model]);

	/* ------------------- Extract and process the bonus sums ------------------- */

	$param_changes = json_decode($params['changes']);
	$param_data = json_decode($params['data']);

	$logger->debug('Decoded JSONs at `changes` and `data` parameters', [
		'changes' => $param_changes,
		'data' => $param_data
	]);

	$old_state = $param_changes->{0}->data;
	$new_state = $param_data;

	$old_sum = $old_state->bonus_sum;
	$new_sum = $new_state->bonus_sum;
	$sum_change = $new_sum - $old_sum;

	$sum_change == 0 ? $logger->fatal(
		'The `bonus_sum` has not changed', 
		['old_sum' => $old_sum,'new_sum' => $new_sum]
	) : $logger->debug(
		'Calculated the bonus sum change', 
		['sum_change' => $sum_change]
	);

	/* --------------------- Send a notification to the user -------------------- */

	$message = $telegram->sendMessage(
		chat_id: $tinybird->query(
				pipe: 'contacts_api',
				params: ['phone_number' => $new_state->num]
			)->{0}->telegram_id,
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
