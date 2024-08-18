#!/usr/bin/env php 
 <?php


require_once __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config/config.php';
$text = require __DIR__ . '/config/texts.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('bstil', [new StreamHandler('php://stdout', Logger::INFO)]);

// $cfg = $config['postgresql'];
// $db = pg_connect("
//     host={$cfg['host']}
//     user={$cfg['user']}
//     password={$cfg['password']}
//     dbname={$cfg['dbname']}
// ");

// интеграция https://business.ru
use bru\api\Client;

$cfg = $config['business_ru'];
$bru = new Client(
    account: $cfg['account'],
    secret: $cfg['secret'],
    app_id: $cfg['app_id'],
    sleepy: true
);

$bru->setLogger(new Logger('bru', [new StreamHandler('php://stdout', Logger::INFO)]));


// интеграция https://telegram.org
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

$cfg = $config['telegram'];
$tg = new Nutgram($cfg['api_key'], new Configuration(
    logger: new Logger('tg', [new StreamHandler('php://stdout', Logger::INFO)])
));
$tg->setRunningMode(Polling::class);


// получение телефонного номера пользователя с запуском бота
$tg->onCommand('start', function(Nutgram $tg) use ($text) {
    $keyboard = new ReplyKeyboardMarkup(
        input_field_placeholder: $text['input_field_placeholder']);
    $keyboard->addRow(
        new KeyboardButton(
            text: $text['request_contact'],
            request_contact: true));

    $tg->sendMessage(
        reply_to_message_id: $tg->messageId(),
        text: $text['start'],
        reply_markup: $keyboard);
});


// запоминание телефонного номера после "/start"
$tg->onContact(function(Nutgram $tg) use ($bru, $logger, $config, $text){
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
 
    // и если он не админ
    if ($vicar && !in_array($message->from->id, $config['telegram']['admins'])) {
        return;
    }

    // если нету бонусной карты или баллов
    if (($cards[0] == null) || ($cards[0]['bonus_sum'] == 0))  {
        $logger->info(
            "A wihout points, or new, user sent contacts", 
            ["user_id" => $message->from->id, "phone" => $phone]);

        $reply = $tg->sendMessage(
            text: $text['no-points'], 
            reply_markup: new ReplyKeyboardRemove(true), 
            reply_to_message_id: $message->message_id);
        
        if (!$vicar) {
            $keyboard = new InlineKeyboardMarkup();
            $keyboard->addRow(new InlineKeyboardButton(
                text: $text['post-no-points-button-text'], 
                url: $text['post-no-points-button-url']));
            
            $tg->sendMessage(
                text: $text['post-no-points'], 
                reply_markup: $keyboard, 
                reply_to_message_id: $reply->message_id);
        }

        return;
    } else {
        $logger->info(
            "User with points sent contacts", 
            ["user_id" => $message->from->id, "phone" => $phone, "bonus_sum" => $cards[0]['bonus_sum']]);

        $tg->sendMessage(
            text: sprintf($text['points'], $cards[0]['bonus_sum']),
            reply_markup: new ReplyKeyboardRemove(true),
            reply_to_message_id: $message->message_id);

        return;
    }
});


$tg->onCommand('help', function(Nutgram $tg) use ($text){
    $tg->sendMessage($text['help']);
});


$tg->run();