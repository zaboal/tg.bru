<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/log.php';

/* Dotenv\Dotenv::createImmutable(__DIR__)->load(); */

// интеграция https://telegram.org
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;
use SergiX44\Nutgram\RunningMode\Functional;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use bru\api\Client;
use bru\api\NoCache;

function handler($event, $context)
{
	# context - useless, $event - encoded data
	$text = require __DIR__ . '/texts.php';
	$logger = new Logger('bstil', [new StreamHandler('php://stdout', Logger::INFO)]);

	$api_key = $_ENV['API_KEY'];

	if ($api_key === false) {
		throw new InvalidArgumentException('No api_key');
	}

	$tg = new Nutgram($api_key, new Configuration(
		logger: new Logger('tg', [new StreamHandler('php://stdout', Logger::INFO)])
	));

	$tg->setRunningMode(Functional::class);

	$bru = new Client(
		account: $_ENV['ACCOUNT'],
		secret: $_ENV['SECRET'],
		app_id: (int) $_ENV['APP_ID'],
		cache: new NoCache(),
		sleepy: true
	);

	$bru->setLogger(new Logger('bru', [new StreamHandler('php://stdout', Logger::INFO)]));

	// получение телефонного номера пользователя с запуском бота
	$tg->onCommand('start', function (Nutgram $tg) use ($text) {
		$keyboard = new ReplyKeyboardMarkup(
			input_field_placeholder: $text['input_field_placeholder']
		);
		$keyboard->addRow(
			new KeyboardButton(
				text: $text['request_contact'],
				request_contact: true
			)
		);

		$tg->sendMessage(
			reply_to_message_id: $tg->messageId(),
			text: $text['start'],
			reply_markup: $keyboard
		);
	});

	$tg->onContact(function (Nutgram $tg) use ($bru, $logger, $text) {
		$message = $tg->message();
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

		$adminsString = $_ENV['admins'] ?? '';

		$adminsArray = $adminsString ? explode(',', $adminsString) : [];

		$admins = array_map('intval', $adminsArray);
		if ($vicar && !in_array($message->from->id, $admins)) {
			return;
		}

		// если нету бонусной карты или баллов
		if (($cards[0] == null) || ($cards[0]['bonus_sum'] == 0)) {
			$logger->info(
				"A wihout points, or new, user sent contacts",
				["user_id" => $message->from->id, "phone" => $phone]
			);

			$reply = $tg->sendMessage(
				text: $text['no-points'],
				reply_markup: new ReplyKeyboardRemove(true),
				reply_to_message_id: $message->message_id
			);

			if (!$vicar) {
				$keyboard = new InlineKeyboardMarkup();
				$keyboard->addRow(new InlineKeyboardButton(
					text: $text['post-no-points-button-text'],
					url: $text['post-no-points-button-url']
				));

				$tg->sendMessage(
					text: $text['post-no-points'],
					reply_markup: $keyboard,
					reply_to_message_id: $reply->message_id
				);
			}

			return;
		} else {
			$logger->info(
				"User with points sent contacts",
				["user_id" => $message->from->id, "phone" => $phone, "bonus_sum" => $cards[0]['bonus_sum']]
			);

			$tg->sendMessage(
				text: sprintf($text['points'], $cards[0]['bonus_sum']),
				reply_markup: new ReplyKeyboardRemove(true),
				reply_to_message_id: $message->message_id
			);

			return;
		}
	});


	$tg->onCommand('help', function (Nutgram $tg) use ($text) {
		$tg->sendMessage($text['help']);
	});

	if (! $event['body']) {
		throw new Exception("No body in mesage");
	}
	/* echo $event['body']; */
	log_message($event['body']);
	$tg->run($event['body']);
}

/* handler(['body' => '{"update_id":562885573,"message":{"message_id":527,"from":{"id":5976605989,"is_bot":false,"first_name":"2happy","username":"gentuwu","language_code":"en","is_premium":true},"chat":{"id":5976605989,"first_name":"2happy","username":"gentuwu","type":"private"},"date":1738247132,"reply_to_message":{"message_id":526,"from":{"id":6869523551,"is_bot":true,"first_name":"\u0412\u0430\u0448\u0438 \u0431\u0430\u043b\u043b\u044b \u0432 \u0411\u0438\u0437\u043d\u0435\u0441 \u0421\u0442\u0438\u043b\u0435","username":"BStilBot"},"chat":{"id":5976605989,"first_name":"2happy","username":"gentuwu","type":"private"},"date":1738247095,"text":"\u041f\u043e\u0434\u0435\u043b\u0438\u0442\u0435\u0441\u044c\u0441\u0432\u043e\u0438\u043c \u0442\u0435\u043b\u0435\u0444\u043e\u043d\u043d\u044b\u043c \u043d\u043e\u043c\u0435\u0440\u043e\u043c, \u0447\u0442\u043e\u0431\u044b\u043c\u044b \u043d\u0430\u0448\u043b\u0438 \u0432\u0430\u0448\u0438 \u0431\u0430\u043b\u043b\u044b"},"contact":{"phone_number":"+79936175103","first_name":"2happy","user_id":5976605989}}}'], 1); */
