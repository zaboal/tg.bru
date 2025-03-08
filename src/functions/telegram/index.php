<?php

require_once __DIR__ . '/vendor/autoload.php'; 
require_once __DIR__ . '/strings.php';
require_once __DIR__ . '/helpers.php';

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;
use SergiX44\Nutgram\Telegram\Types\Keyboard\{
	InlineKeyboardButton,
	InlineKeyboardMarkup,
	KeyboardButton,
	ReplyKeyboardMarkup,
	ReplyKeyboardRemove};
use bru\api\Client;

function handler($event, $context)
{
	$tinybird = new TinybirdClient(
		token: $_ENV['TINYBIRD_TOKEN'],
		baseUrl: 'https://api.europe-west3.gcp.tinybird.co/v0');
	$telegram = new Nutgram($_ENV['API_KEY']);
	$bru = new Client(
		account: $_ENV['ACCOUNT'],
		secret: $_ENV['SECRET'],
		app_id: (int) $_ENV['APP_ID'],
		sleepy: true
	);

	$telegram->setRunningMode(Webhook::class);

	/* ------------- Declaring and registering Telegram Bot Commands ------------ */

	$telegram->onCommand('start', function (Nutgram $telegram) use ($strings) {
		$keyboard = new ReplyKeyboardMarkup(
			input_field_placeholder: $strings['input_field_placeholder']
		);
		$keyboard->addRow(
			new KeyboardButton(
				text: $strings['request_contact'],
				request_contact: true
			)
		);

		$telegram->sendMessage(
			reply_to_message_id: $telegram->messageId(),
			text: $strings['start'],
			reply_markup: $keyboard
		);
	});

	$telegram->onContact(function (Nutgram $telegram) use ($bru, $strings) {
		$message = $telegram->message();
		$contact = $message->contact;
		$phone = $contact->phone_number;
		$phone = substr($phone, strlen($phone) - 10); # оставить последние десять цифр номера, т.е. без +7

		$cards = $bru->request('get', 'discountcards', ['num' => $phone])['result'];

		// если телефонный номер не пренадлежит пользователю
		if ($message->from->id != $message->contact->user_id) {
			$vicar = true;
		} else {
			$vicar = false;
		}

		$adminsString = $_ENV['ADMINS'] ?? '';

		$adminsArray = $adminsString ? explode(',', $adminsString) : [];

		$admins = array_map('intval', $adminsArray);
		if ($vicar && !in_array($message->from->id, $admins)) {
			return;
		}

		// если нету бонусной карты или баллов
		if (($cards[0] == null) || ($cards[0]['bonus_sum'] == 0)) {

			$reply = $telegram->sendMessage(
				text: $strings['no-points'],
				reply_markup: new ReplyKeyboardRemove(true),
				reply_to_message_id: $message->message_id
			);

			if (!$vicar) {
				$keyboard = new InlineKeyboardMarkup();
				$keyboard->addRow(new InlineKeyboardButton(
					text: $strings['post-no-points-button-text'],
					url: $strings['post-no-points-button-url']
				));

				$telegram->sendMessage(
					text: $strings['post-no-points'],
					reply_markup: $keyboard,
					reply_to_message_id: $reply->message_id
				);
			}

			return;
		} else {
			$telegram->sendMessage(
				text: sprintf($strings['points'], $cards[0]['bonus_sum']),
				reply_markup: new ReplyKeyboardRemove(true),
				reply_to_message_id: $message->message_id
			);

			return;
		}
	});

	$telegram->onCommand('help', function (Nutgram $telegram) use ($strings) {
		$telegram->sendMessage($strings['help']);
	});

	/* ------- Sharing the data with the database and replying to the user ------ */

	$tinybird->sendEvent(
		datasource: 'messages_raw',
		data: json_decode($event['body'], true)
	);

	$telegram->run($event['body']);
}