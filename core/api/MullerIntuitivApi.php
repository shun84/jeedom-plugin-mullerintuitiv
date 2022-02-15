<?php

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class MullerIntuitivApi
{
    protected const URL = 'https://app.muller-intuitiv.net';
    protected const CLIENT_ID = 'NTllNjA0OTQ4ZmUyODNmZDRkYzdlMzU1';
    protected const CLIENT_SECRET = 'ckFlV3U4WTNZcVhFUHFSSjRCcEZ6Rkc5OE1SWHBDY3o=';
    private $username;
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getOauth(): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/oauth2/token',[
            'form_params' => [
                'client_id' => base64_decode(self::CLIENT_ID),
                'user_prefix' => 'muller',
                'client_secret' => base64_decode(self::CLIENT_SECRET),
                'grant_type' => 'password',
                'scope' => 'read_muller write_muller',
                'password' => $this->getPassword(),
                'username' => $this->getUsername()
            ]
        ]);
    }

    public function getToken(): string
    {
        $reponse = $this->getOauth();

        $getoauth = $reponse->getBody()->getContents();
        $accesstoken = json_decode($getoauth, true);

        return $accesstoken['access_token'];
    }

    public function getHomeId()
    {
        $client = new Client();
        $reponse = $client->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['id'];
    }

    public function getHomeName()
    {
        $client = new Client();
        $reponse = $client->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['name'];
    }

    public function getModeHome(){
        $client = new Client();
        $reponse = $client->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['therm_mode'];
    }

    public function setModeHome(string $modehome): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/api/setthermmode',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'mode' => $modehome,
                'home_id' => $this->getHomeId()
            ]
        ]);
    }

    public function getRoomsIdAndName(): array
    {
        $client = new Client();
        $reponse = $client->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $roomname = json_decode($getoauth, true);
        $roomname = $roomname['body']['homes'][0]['rooms'];

        $idandname = [];
        foreach ($roomname as $value){
            $idandname[] = $value;
        }
        return $idandname;
    }

    public function getRooms(){
        $client = new Client();
        $reponse = $client->request('POST',self::URL.'/syncapi/v1/homestatus',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'home_id' => $this->getHomeId()
            ]
        ]);

        if ($reponse->getStatusCode() != '200'){
            return $reponse->getStatusCode();
        }
        $getoauth = $reponse->getBody()->getContents();
        $rooms = json_decode($getoauth, true);

        return $rooms['body']['home']['rooms'];
    }

    public function setTemperature(string $roomid, float $roomtemp): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'therm_setpoint_mode' => 'manual',
                            'therm_setpoint_temperature' => $roomtemp,
                            'id' => $roomid,
                            'therm_setpoint_end_time' => strtotime("now")
                        ]
                    ],
                    'id' => $this->getHomeId()
                ]
            ]
        ]);
    }

    public function setRoomHome(string $roomid): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'boost' => false,
                            'therm_setpoint_mode' => 'home',
                            'id' => $roomid
                        ]
                    ],
                    'id' => $this->getHomeId()
                ]
            ]
        ]);
    }

    public function setRoomHorsGel(string $roomid): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'therm_setpoint_mode' => 'hg',
                            'id' => $roomid
                        ]
                    ],
                    'id' => $this->getHomeId()
                ]
            ]
        ]);
    }

    public function setWindows(string $roomid,bool $windows): ResponseInterface
    {
        $client = new Client();

        return $client->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'open_window' => $windows,
                            'id' => $roomid
                        ]
                    ],
                    'id' => $this->getHomeId()
                ]
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}