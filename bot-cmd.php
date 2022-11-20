<?php

use Deljdlx\DiscordBot\Bot;

require __DIR__ . '/src/Bot.php';

$token = 'MTA0MzI0MjQyNTY1ODkxMjc4OA.G6FZne.vsrjD15NIn9z2I6VPa4Yi3KwEvrBZdoU1cjXzg';
$channelId = '753209856856686656';
// $channelId = '1043216701875028119';
$bot = new Bot($token);

// ===========================================================


$bot->registerCommand('!ping', function($message) use ($bot) {
    print_r($message);
});

// ===========================================================

$bot->registerCommand('!repeat', function($message) use ($bot) {
    $channelId = $message->channel_id;
    $content = preg_replace('/^!repeat/', '', $message->content);
    $bot->sendMessage($channelId, $content);
    return $message;
});

// ===========================================================

$bot->registerCommand('!ben', function($message) use ($bot) {
    $channelId = $message->channel_id;
    $content = '
        ```
        Ben et Bob sont sur un bateau
        Ben tombe à l\'eau.
        
        Pourquoi ?
        ```
        !1) <@149517574156189697> était bourré
        !2) <@149517574156189697> a trop travaillé sur le dossier de son titre pro et il était fatigué
        !3) <@149517574156189697> s\'est pris pour Bob - l\'éponge (et donc il était bourré)
    ';

    $bot->sendMessage($channelId, $content);
    $bot->setState('quizStarted', true);
    return $message;
});


$bot->registerCommand(null, function($message) use ($bot) {
    
    $channelId = $message->channel_id;

    echo "Watch" . PHP_EOL;
    
    print_r($message->content);

    if($bot->getState('quizStarted') === true && preg_match('/^!/', $message->content) && !preg_match('/^!ben/', $message->content)) {
        if($message->content == '!2') {
            $bot->sendMessage($channelId, $message->author->username . " ne connait pas bien Ben");
        }
        else {
            $bot->sendMessage($channelId, $message->author->username . " a trouvé la bonne réponse");
            
        }

        $bot->setState('quizStarted', false);
        $bot->sendMessage($channelId, "Fin de la devinette");
    }
});



// ===========================================================


$bot->registerCommand('!exec', function($message) use ($bot) {
    // $channelId = $message->channel_id;
    $content = preg_replace('/^!exec/', '', $message->content);
    exec($content, $data);
    echo implode("\n", $data);
});

$bot->registerCommand('!sms', function($message) use ($bot) {

    $users = [
        '#julien' => '0625181355',
        '#clemence' => '0626904472',
        '#ben' => '0650543244',
    ];


    $content = trim(preg_replace('/^!sms/', '', $message->content));

    $data = explode(" ", $content);
    $phoneIdentifier = array_shift($data);
    $phoneNumber = $users[$phoneIdentifier];
    
    $content = implode(' ', $data);

    $url = 'http://192.168.1.22:8090/SendSMS?username=elbiniou&password=aaaa&phone='.$phoneNumber.'&message=' . rawurlencode($content);
    file_get_contents($url);


    $channelId = $message->channel_id;
    $bot->sendMessage($channelId, 'Message sent to ' . $phoneNumber . ' : ' . $content . ' (' . $url . ')');

});






$bot->watchChannel($channelId);
