<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config.php';
require __DIR__.'/../liberes/Redmine.php';
require __DIR__.'/../data.php';
require __DIR__.'/../liberes/functions.php';
// космический спутник просто
ini_set("memory_limit", "128M");
ini_set("max_execution_time", 3600); // Удобен для схемы нескольки ядерных процессоров

use Telegram\Bot\Api;
$telegram = new Api($config['bots']['HostWay']['token']);

$redis = new Redis();
//Connecting to Redis
$redis->connect('127.0.0.1', '6379');
$redmine = new \Redmine($redis);

$data = $redis->exists("messages_list") ? json_decode($redis->get("messages_list"), true) : [];

// save messages
if (!empty($_POST['text'])) {
    file_put_contents(__DIR__.'/../uploads/'.$_POST['username'].'.txt', $_POST['text']);
    $issue = $redis->exists($_POST['username']) ? json_decode($redis->get($_POST['username']), true) : [];

    if ($issue) {
        foreach($issue as $value) {          
            $redmine->updateIssue($redmine->getProjectIdByName('Продажа-БОТ'), $value, 'https://' . IP_SERVER . '/uploads/' . $_POST['username'].'.txt');
            $redis->del('sayning_' . $_POST['username']);
        }
    }
    

    echo '/uploads/' . $_POST['username'].'.txt'; exit;
}

$chatId = null;
if (isset($data[count($data)-1]['message']['chat']['id'])) {
    $chatId = $data[count($data)-1]['message']['chat']['id'];
}
if (isset($data[count($data)-1][0]['message']['chat']['id'])) {
    $chatId = $data[count($data)-1][0]['message']['chat']['id'];
}
$supportUserList = $redis->exists('support_user_list') ? json_decode($redis->get('support_user_list')) : [];

// send support users
if (!empty($_POST['send_support'])) {
    
    foreach($supportUserList as $username) {
        $response = $telegram->sendMessage([
            'from_chat_id' => $username,
            'chat_id' => isset($_POST['chat_id']) ? $_POST['chat_id'] : $chatId,
            'text' => $_POST['msg']
        ]);
        
        if ($msg = $response->getMessageId()) {
            $data[] = [
                'support' => true,
                'group' => true,
                //'support' => in_array($items['message']['from']['username'], $supportUserList),
                'date_format' => (new \DateTime)->setTimestamp($response['date'])->format('d.m.Y H:i:s'), 
                'message' => $response
            ];
            $redis->set("messages_list", json_encode($data), $cacheSize);
        }
    }
    exit;
}

// send all users
if (empty($_POST['send_support']) && empty($_POST['username']) && isset($_POST['msg']) && isset($chatId)) {
    $response = $telegram->sendMessage([
            'chat_id' =>$chatId,
            'text' => $_POST['msg']
        ]);
        if ($msg = $response->getMessageId()) {
            $data[] = [
                'support' => false,
                'group' => true,
                //'support' => in_array($items['message']['from']['username'], $supportUserList),
                'date_format' => (new \DateTime)->setTimestamp($response['date'])->format('d.m.Y H:i:s'), 
                'message' => $response
            ];

            $redis->set("messages_list", json_encode($data), $cacheSize);
        }
    exit;
}

// переписка через бот
if(!empty($_POST['from_chat_id']) && !empty($_POST['msg'])) {
    $response = $telegram->sendMessage([
        'from_chat_id' => $_POST['from_chat_id'],
        'chat_id' => $_POST['chat_id'],
        'text' => $_POST['msg']
    ]);
    if ($msg = $response->getMessageId()) {
        if ($redis->exists('sayning_' . $_POST['from_chat_id'])) {
            $saydings = json_decode($redis->get('sayning_' . $_POST['from_chat_id']), true);
            
            $saydings[] = [
                'from_chat_id' => $_POST['from_chat_id'],
                'chat_id' =>$_POST['chat_id'],
                'text' => $_POST['msg'],
                'date_send' => (new \DateTime)->getTimestamp(),
                'msg_id' => $data[count($data) - 1]['message']['message_id'],
            ];
        }
        else { 
            $saydings = [];
            $saydings[] = [
                'from_chat_id' => $_POST['from_chat_id'],
                'chat_id' => $_POST['chat_id'],
                'text' => $_POST['msg'],
                'date_send' => (new \DateTime)->getTimestamp(),
                'msg_id' => $data[count($data) - 1]['message']['message_id'],
            ];
        }
            

        $redis->set('sayning_' . $_POST['from_chat_id'], json_encode($saydings));
    }

    echo $msg;
    exit;
}

// вся переписка
if(!empty($_GET['username'])) {
 
    $result = [];
    foreach($data as $keyMsg => &$items) {

        $username = isset($items['message']['from']['username']) ? $items['message']['from']['username'] : '';
        if ($_GET['username'] != $username) continue;
        if (in_array($username, $supportUserList) && $_GET['username'] != $username) continue;

        if (in_array($username, $supportUserList) && $_GET['username'] != $username) continue;

        $text = strtolower($items['message']['text']);
        $text = preg_replace("#\s+#", " ", $text);
        $text = preg_replace("#cent\s+os#", "centos", $text);
        $text = trim($text);

        if (!empty($items['message']['date'])) {
            $data[$keyMsg]['date_format'] = (new \DateTime)->setTimestamp($items['message']['date'])->format('d.m.Y H:i:s');
        }
        if (!empty($items['callback_query']['message']['date'])) { 
            $items['callback_query']['date_format'] = (new \DateTime)->setTimestamp($items['callback_query']['message']['date'])->format('d.m.Y H:i:s');
        }

        $flagResponce = false;
        
        // заведение заявки
        foreach ($messagesTicket as $key => $message) {
            
            $analize = (similarity($text, $message) * 100);
            if ($analize > 50) {                                       
                $textResponce = 'Заявка оформляеться: ' . $text;
                $flagResponce = true;
                break;
            }
            
        }

        if ($text == "/start") {
            $textResponce = 'Бот стартанул.';
            $flagResponce = true;
        }

        // запрос support
        if (!$flagResponce) {
            $i = 0;
            while(!$flagResponce && isset($messagesSupport[$i])) {
            
                if((similarity($messagesSupport[$i++], $text) * 100) >= 50) {
                    $textResponce = "Связываю со специалистом.";
                    $flagResponce = true;
                    // Связь с Support
                }
            }
        }

        // bot не понял вопроса и ответил
        if (!$flagResponce && !$redis->exists('sayning_' . $chatId)) {
            $textResponce = "Я не понимаю Вас.\nСвяжитесь со специалистом.";
            $flagResponce = true;
        }

        // operator messaging
        if ($saydings = json_decode($redis->get('sayning_' . $chatId))) {

            if (isset($items['message']['message_id']) && is_object($saydings) && $saydings->msg_id == $items['message']['message_id']) {
                $result[] = ['bot_responce' => $saydings->text];
            }
            else {
                foreach($saydings as $msgs) {
                    if ($msgs->msg_id == $items['message']['message_id'])
                        $result[] = ['bot_responce' => $msgs->text];
                }
            }

            $flagResponce = true;
        }
	

    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data); exit;

}
?>
