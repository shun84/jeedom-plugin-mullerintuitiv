<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class mullerintuitivApi
{
    protected const URL = 'https://app.muller-intuitiv.net';
    protected const CLIENT_ID = 'NTllNjA0OTQ4ZmUyODNmZDRkYzdlMzU1';
    protected const CLIENT_SECRET = 'ckFlV3U4WTNZcVhFUHFSSjRCcEZ6Rkc5OE1SWHBDY3o=';
    private $username;
    private $password;
    private $client;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     */
    public function getOauth(): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/oauth2/token',[
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

    /**
     * @throws GuzzleException
     */
    public function getToken(): string
    {
        $reponse = $this->getOauth();

        $getoauth = $reponse->getBody()->getContents();
        $accesstoken = json_decode($getoauth, true);

        return $accesstoken['access_token'];
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeId()
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['id'];
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeName()
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['name'];
    }

    /**
     * @throws GuzzleException
     */
    public function getModeHome(){
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept'        => 'application/json',
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['therm_mode'];
    }

    /**
     * @throws GuzzleException
     */
    public function setModeHome(string $modehome): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/setthermmode',[
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

    /**
     * @throws GuzzleException
     */
    public function getRoomsIdAndName(): array
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
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

    /**
     * @throws GuzzleException
     */
    public function getRooms(){
        $reponse = $this->getClient()->request('POST', self::URL . '/syncapi/v1/homestatus', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
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

    /**
     * @throws GuzzleException
     */
    public function setTemperature(string $roomid, float $roomtemp): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
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

    /**
     * @throws GuzzleException
     */
    public function setRoomHome(string $roomid): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
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

    /**
     * @throws GuzzleException
     */
    public function setRoomHorsGel(string $roomid): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
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

    /**
     * @throws GuzzleException
     */
    public function setWindows(string $roomid, bool $windows): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
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

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}