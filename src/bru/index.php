<?php

require_once __DIR__ . '/vendor/autoload.php';
/* require_once __DIR__ . '/log.php'; */

Dotenv\Dotenv::createImmutable(__DIR__)->load();

// интеграция https://telegram.org
use bru\api\Client;
use bru\api\NoCache;

function handler($event, $context)
{
	# context - useless, $event - encoded data
	if (! $event['body']) {
		throw new Exception("No body in mesage");
	}
	$текст = base64_decode($event['body'], true);
	parse_str($текст, $params);
	$changes = json_decode($params['changes'], true);
	$new = $changes[1]['data']['bonus_sum'];
	$delta = $new - $changes[0]['data']['bonus_sum'];
	$text = "Благодарим за покупку!\nНачислено " . $new . " баллов, теперь у Вас " . $delta . " баллов";
	echo $text;

	$api = $_ENV['API_KEY'];
	$url = "https://api.telegram.org/bot$api/sendMessage";
	echo $url;
	
	/* echo $event['body']; */
	/* log_message($event['body']); */
}

handler(['body' => 'YXBwX2lkPTk2ODQyNiZtb2RlbD1kaXNjb3VudGNhcmRzJmFjdGlvbj11cGQmY2hhbmdlcz0lN0IlMjIwJTIyJTNBJTdCJTIyZW1wbG95ZWVfcmVmJTIyJTNBbnVsbCUyQyUyMmFjdGlvbiUyMiUzQSUyMnVwZCUyMiUyQyUyMmRhdGElMjIlM0ElN0IlMjJpZCUyMiUzQW51bGwlMkMlMjJudW0lMjIlM0FudWxsJTJDJTIyZGlzY291bnRfY2FyZF90eXBlX2lkJTIyJTNBbnVsbCUyQyUyMnBhcnRuZXJfaWQlMjIlM0FudWxsJTJDJTIyZGF0ZV9iZWdpbiUyMiUzQW51bGwlMkMlMjJkYXRlX2VuZCUyMiUzQW51bGwlMkMlMjJjdXJyZW50X2Rpc2NvdW50X3ZhbHVlJTIyJTNBbnVsbCUyQyUyMnN1bV9jdXJyZW50JTIyJTNBbnVsbCUyQyUyMnN1bV9maXQlMjIlM0FudWxsJTJDJTIyYm9udXNfc3VtJTIyJTNBMTAzNCUyQyUyMnVwZGF0ZWQlMjIlM0FudWxsJTJDJTIyZGVsZXRlZCUyMiUzQW51bGwlN0QlN0QlMkMlMjIxJTIyJTNBJTdCJTIyZW1wbG95ZWVfcmVmJTIyJTNBbnVsbCUyQyUyMmFjdGlvbiUyMiUzQSUyMnVwZCUyMiUyQyUyMmRhdGElMjIlM0ElN0IlMjJpZCUyMiUzQW51bGwlMkMlMjJudW0lMjIlM0FudWxsJTJDJTIyZGlzY291bnRfY2FyZF90eXBlX2lkJTIyJTNBbnVsbCUyQyUyMnBhcnRuZXJfaWQlMjIlM0FudWxsJTJDJTIyZGF0ZV9iZWdpbiUyMiUzQW51bGwlMkMlMjJkYXRlX2VuZCUyMiUzQW51bGwlMkMlMjJjdXJyZW50X2Rpc2NvdW50X3ZhbHVlJTIyJTNBbnVsbCUyQyUyMnN1bV9jdXJyZW50JTIyJTNBbnVsbCUyQyUyMnN1bV9maXQlMjIlM0FudWxsJTJDJTIyYm9udXNfc3VtJTIyJTNBMTEzNCUyQyUyMnVwZGF0ZWQlMjIlM0FudWxsJTJDJTIyZGVsZXRlZCUyMiUzQW51bGwlN0QlN0QlN0QmZGF0YT0lN0IlMjJpZCUyMiUzQTE3MTg2JTJDJTIybnVtJTIyJTNBJTIyOTkyMTk1NTEyNyUyMiUyQyUyMmRpc2NvdW50X2NhcmRfdHlwZV9pZCUyMiUzQTglMkMlMjJwYXJ0bmVyX2lkJTIyJTNBbnVsbCUyQyUyMmRhdGVfYmVnaW4lMjIlM0FudWxsJTJDJTIyZGF0ZV9lbmQlMjIlM0FudWxsJTJDJTIyY3VycmVudF9kaXNjb3VudF92YWx1ZSUyMiUzQSUyMjUlMjIlMkMlMjJzdW1fY3VycmVudCUyMiUzQSUyMjAlMjIlMkMlMjJzdW1fZml0JTIyJTNBJTIyMCUyMiUyQyUyMmJvbnVzX3N1bSUyMiUzQSUyMjEyMzQlMjIlMkMlMjJ1cGRhdGVkJTIyJTNBJTIyMDQuMDIuMjAyNSswMCUzQTM1JTNBNDguNTI5NDcyJTIyJTJDJTIyZGVsZXRlZCUyMiUzQWZhbHNlJTdEJmFwcF9wc3c9Y2E1YzYwNjBlNjE1YTJhM2NkZDNlMmMyNzEyZGMwZDM='], 1);
