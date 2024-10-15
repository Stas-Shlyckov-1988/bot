<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config.php';

use Telegram\Bot\Api;

if (file_exists(__DIR__ . '/../ban_list.json')) {
    $banList = json_decode(file_get_contents(__DIR__ . '/../ban_list.json'), true);
}
else {
    $banList = [];
}

if (!empty($_GET['isban'])) {
    $isBan = 0;
    if ($banList)
        foreach($banList as $userChat) {
            if ($userChat['username'] == $_GET['username']) $isBan = 1;
        }
    echo $isBan; exit;
}

$telegram = new Api($config['bots']['HostWay']['token']);
$data = $telegram->getUpdates();
foreach($data as $items) {
    if (isset($items['callback_query']['data']) && $items['callback_query']['message']['from']['username'] == $_GET['username']) {
        $banList[] = $items['callback_query']['message']['from'];
        break;
    }
    else if($items['message']['from']['username'] == $_GET['username']) {
        $banList[] = $items['message']['from'];
        break;
    }
}

file_put_contents(__DIR__ . '/../ban_list.json', json_encode($banList));
echo 1;
?>