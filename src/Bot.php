<?php

namespace Deljdlx\DiscordBot;

class Bot
{
    private $token;
    private $userAgent = 'Discord Bot(https://discord-not.local)';
    private $apiRootUrl = 'https://discord.com/api/v10';
    private $sleepTime = 2;

    private $commands = [];

    private $applicationData;

    private $state = [];

    public function __construct(
        $token,
        $apiRootUrl = 'https://discord.com/api/v10',
        $userAgent = 'Discord Bot(https://localhost.dev)'
    ) {
        $this->token = $token;
        $this->apiRootUrl = $apiRootUrl;
        $this->userAgent = $userAgent;

        $this->loadBotData();
    }

    public function getId() {
        return $this->applicationData->id;
    }

    public function setState($name, $value)
    {
        $this->state[$name] = $value;
        return $this;
    }

    public function getState($name)
    {
        if(!isset($this->state[$name])) {
            return null;
        }
        return $this->state[$name];
    }

    public function loadBotData()
    {
        $url = $this->apiRootUrl . '/oauth2/applications/@me';
        $data = $this->httpQuery($url);
        $this->applicationData = json_decode($data);
        return $this;
    }

    public function registerCommand($command = null, $callback = null)
    {
        if($command !== null) {
            $this->commands[$command] = $callback;
        }
        else {
            $this->commands[] = $callback;
        }
        
        return $this;
    }

    public function getMessages($url)
    {
        $response = $this->httpQuery(
            $url,
        );
    
        return json_decode($response);
    }
    
    // todo handle no messages
    public function getLastMessage($channelId)
    {
        $url = $this->apiRootUrl . '/channels/' . $channelId . '/messages';
        $messages = $this->getMessages($url);
        if(array_key_exists(0, $messages)) {
            return $messages[0];
        }
        return false;
        
    }

    public function getLastMessageContent($url)
    {
        return $this->getLastMessage($url)->content;
    }

    public function sendMessage($channelId, $message)
    {

        $url = $this->apiRootUrl . '/channels/' . $channelId . '/messages';

        $response = $this->httpQuery(
            $url,
            'POST',
            ['content'=> $message,],
            [
                'Authorization' => 'Bot ' . $this->token,
                'User-Agent' => $this->userAgent,
            ]
        );
        return json_decode($response);
    }

    public function cestPasFaux($url)
    {
        return $this->sendMessage("C'est pas faux", $url, $this->token, $this->userAgent);
    }

    public function watchChannel($channelId) 
    {
        $lastId = null;
        

        while(true) {
            sleep($this->sleepTime);
         
            $message = $this->getLastMessage($channelId);

            if(!$message->id) {
                continue;
            }

            if($message->id == $lastId) {
                continue;
            }
        
            if($message->author->id == $this->getId()) {
                continue;
            }

            $lastId = $message->id;

            foreach ($this->commands as $command => $callback) {
                if(preg_match('/^'.$command.'\b/', $message->content)) {
                    $callback($message);
                }
                else if(is_int($command)) {
                    $callback($message);
                }
            }
        }
    }

    private function httpQuery($url, $method='get', $data=array(), $headers=array()) {

        $method=strtoupper($method);
    
        $headerString='';
        $contentTypeHeader=false;

        $headers = array_merge(
            [
                'Authorization' => 'Bot ' . $this->token,
                'User-Agent' => $this->userAgent
            ],
            $headers,
        );
        
        foreach ($headers as $name=>$value) {
            if($name=='Content-type') {
                $contentTypeHeader=true;
            }
            $headerString.=$name.': '.$value."\r\n";
        }
        
        if(!$contentTypeHeader && $method=='POST') {
            $headerString='Content-type: application/x-www-form-urlencoded'."\r\n".$headerString;
        }
    
        $raw=http_build_query($data);
        
        $options = array(
            'http' => array(
                'header'  => $headerString."Content-Length: ".strlen($raw)."\r\n",
                'method'  => $method,
                'content' => $raw,
                'request_fulluri' => true
            ),
        );
        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
}
