<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/liberes/Redmine.php';
require __DIR__.'/liberes/Lk.php';
require __DIR__.'/data.php';
require __DIR__.'/liberes/functions.php';
// космический спутник просто
ini_set("memory_limit", "128M");
ini_set("max_execution_time", 3600); // Удобен для схемы нескольки ядерных процессоров

use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;

//$telegram = new BotsManager($config);
//$userBot = $telegram->bot('HostWay')->getMe();
$telegram = new Api($config['bots']['HostWay']['token']);

$redis = new Redis();
//Connecting to Redis
$redis->connect('127.0.0.1', '6379');

$redmine = (new \Redmine())->setCache($redis);
//$redis->del('bot_projects'); die;
$lk = new Lk;

// cleaning from the chat by telegram of the automatic
$data = [json_decode(file_get_contents('php://input'), true)];
$banList = json_decode(file_get_contents(__DIR__ . '/ban_list.json'), true);

foreach($data as $items) {

    if ((isset($items['message']['text']) && $items['message']['text'] == '/start') || is_null($items)) continue;
    
    $messagesCacheList = $redis->exists("messages_list") ? json_decode($redis->get("messages_list"), true) : [];
    $messagesCacheList[] = $items;
    $redis->set("messages_list", json_encode($messagesCacheList), $cacheSize);

    if (isset($items['callback_query']['data'])) {
        // ban list
        if (!empty($banList))
        foreach ($banList as $chatUser) {
            if($chatUser['id'] == $items['callback_query']['message']['from']['id']) continue 2;
        }
       // отбработка кнопок, ответы
       if ($redis->exists($items['callback_query']['message']['message_id'])) continue;

       $user = $lk->getUser($items['callback_query']['message']['from']['username']);

        switch($items['callback_query']['data']) {
            case 'install_os':
                $description = "Покупка сервера ОС: " . str_replace('install_', '', $items['callback_query']['data']) . PHP_EOL;
                $description .= "заказчик: " .  $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $description .= "url: https://" . IP_SERVER . '/chat.php?username=' . $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $issue = $redmine->issue($redmine->getProjectIdByName('Продажа-БОТ'), [
                    'subject' => "Продажа сервера", 
                    'description' => $description,
                ]);
                if (!empty($issue['issue']['id'])) {
                    $issueIds = $redis->exists($items['callback_query']['message']['chat']['username']) ? json_decode($redis->get($items['callback_query']['message']['chat']['username'])) : [];
                    $issueIds[] = $issue['issue']['id'];
                    $redis->set($items['callback_query']['message']['chat']['username'], json_encode($issueIds), $cacheSize);
                }
                //$lk->serverOsInstall(['server_id' => $user['servers']['id'], 'user_id' => $user['user']['id'], 'os_id' => 1]);
                $response = $telegram->sendMessage([
                    'from_chat_id' => $items['callback_query']['message']['from']['username'],
                    'chat_id' => $items['callback_query']['message']['chat']['id'],
                    'text' => 'Заявка оформляеться: продажа сервера ' . str_replace('install_', '', $items['callback_query']['data'])
                ]);
                break;
            case 'support':
                $description = "Заказчик: " .  $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $description .= "url: https://" . IP_SERVER . '/chat.php?username=' . $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $redmine->issue($redmine->getProjectIdByName('Саппорт-БОТ'), ["subject" => "Саппорт", 'description' => $description]);
                $response = $telegram->sendMessage([
                    'from_chat_id' =>  $items['callback_query']['message']['from']['username'],
                    'chat_id' => $items['callback_query']['message']['chat']['id'],
                    'text' => "Связываю со специалистом."
                ]);
                $supportUserList = $redis->exists('support_user_list') ? json_decode($redis->get('support_user_list')) : [];
                $supportUserList[] = $items['callback_query']['message']['chat']['username'];
                $redis->set('support_user_list', json_encode($supportUserList), $cacheSize);
                break;
            case 'payment_server':
                $description = "Заказчик: " .  $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $description .= "url: https://" . IP_SERVER . '/chat.php?username=' . $items['callback_query']['message']['chat']['username'] . PHP_EOL;
                $redmine->issue($redmine->getProjectIdByName('Продажа-БОТ'), ['subject' => 'Оплатить сервер', 'description' => $description]);
                $response = $telegram->sendMessage([
                    'from_chat_id' =>  $items['callback_query']['message']['from']['username'],
                    'chat_id' => $items['callback_query']['message']['chat']['id'],
                    'text' => "Заявка на оплату сервера."
                ]);
                break;
        }
        $redis->set($items['callback_query']['message']['message_id'], $items['callback_query']['data'], $cacheSize);
        exit;
    }

    // ban list
    if (!empty($banList))
    foreach ($banList as $chatUser) {
        if($chatUser['id'] == $items['message']['from']['id']) continue 2;
    }
    
    $redisKey = $items['message']['message_id'];
    if ($redis->exists('sayning_' . $items['message']['chat']['id'])) continue;
    if ($redis->exists($redisKey)) continue;
    //$redis->del($redisKey);
    $text = strtolower($items['message']['text']);
    $text = preg_replace("#\s+#", " ", $text);
    $text = preg_replace("#cent\s+os#", "centos", $text);
    $text = trim($text);

    $user = $lk->getUser($items['message']['from']['username']);

    // заведение заявки
    foreach ($messagesTicket as $key => $message) {
        
        $analize = (similarity($text, $message) * 100);
        if ($analize > 50) {
            $redis->set($redisKey, $text, $cacheSize);
                        
            $response = $telegram->sendMessage([
                'from_chat_id' => $items['message']['from']['username'],
                'chat_id' => $items['message']['chat']['id'],
                'text' => 'Заявка оформляеться: ' . $text
            ]);
            $fromChatId = $items['message']['from']['id'];

            if ($messageId = $response->getMessageId()) {
                // ddos telegram firewall
                $description = "Заказчик: " . $items['message']['chat']['first_name'] . " " . $items['message']['chat']['last_name'] . PHP_EOL;
                $description .= "username: @" . $items['message']['chat']['username'] . PHP_EOL;
                $description .= "url: https://" . IP_SERVER . '/chat.php?username=' . $items['message']['chat']['username'] . PHP_EOL;
                $description .= "Список сообщенией пользователя" . PHP_EOL;
                foreach ($messageList as $tickets) {
                    if ($fromChatId == $tickets['message']['from']['id'] && !$redis->exists($tickets['message']['message_id'])) {
                        $redis->set($tickets['message']['message_id'], $text, $cacheSize);
                        $description .= (new \DateTime)->setTimestamp($tickets['message']['date'])->format('d.m.Y H:i:s') . ' ' . $tickets['message']['text'] . PHP_EOL;
                    }
                }
                $redmine->issue($redmine->getProjectIdByName('Продажа-БОТ'), [
                    'subject' => "{$analize}% " . $text, 
                    'description' => $description,
                    'username' => $items['message']['from']['username'],
                ]);
                break;
            }
            
        }
        
    }

    // запрос support
    foreach($messagesSupport as $msg) {
        
        if((similarity($text, $msg) * 100) >= 50) {
            $redis->set($redisKey, $text, $cacheSize);

            $response = $telegram->sendMessage([
                'from_chat_id' =>  $items['message']['from']['username'],
                'chat_id' => $items['message']['chat']['id'],
                'text' => "Связываю со специалистом."
            ]);
            $fromChatId = $items['message']['from']['id'];
            // Связь с Support
            if ($messageId = $response->getMessageId()) {
                // ddos telegram firewall
                $description = "Заказчик: " . $items['message']['chat']['first_name'] . " " . $items['message']['chat']['last_name'] . PHP_EOL;
                $description .= "username: @" . $items['message']['chat']['username'] . PHP_EOL;
                $description .= "url: https://" . IP_SERVER . '/chat.php?username=' . $items['message']['chat']['username'] . PHP_EOL;
                $description .= "Список сообщенией пользователя" . PHP_EOL;
                foreach ($messageList as $supports) {
                    if ($fromChatId == $supports['message']['from']['id'] && !$redis->exists($supports['message']['message_id'])) {
                        $redis->set($supports['message']['message_id'], $text, $cacheSize);
                        $description .= (new \DateTime)->setTimestamp($supports['message']['date'])->format('d.m.Y H:i:s') . ' ' . $supports['message']['text'] . PHP_EOL;
                    }
                }
                $redmine->issue($redmine->getProjectIdByName('Саппорт-БОТ'), ['subject'=> $text, 'description' => $description]);
                $supportUserList = $redis->exists('support_user_list') ? json_decode($redis->get('support_user_list')) : [];
                $supportUserList[] = $items['message']['chat']['username'];
                $redis->set('support_user_list', json_encode($supportUserList), $cacheSize);
                break;
            }
        }
    }

    // bot не понял вопроса и ответил
    if (!isset($messageId) && isset($text)) {
        $telegram->sendMessage([
            'from_chat_id' => $items['message']['from']['username'],
            'chat_id' => $items['message']['chat']['id'],
            'text' => "Я бот. Я не понимаю Вас.\nСвязаться со специалистом или выполнить заказ.",
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'Установка ОС',
                            'callback_data' => 'install_os',
                        ),
            
                        array(
                            'text' => 'Оплатить сервер',
                            'callback_data' => 'payment_server',
                        ),

                        array(
                            'text' => 'Саппорт',
                            'callback_data' => 'support',
                        ),
                    )
                ),
            )),
        ]);

        $redis->set($redisKey, $text, $cacheSize);
    }

}


?>
