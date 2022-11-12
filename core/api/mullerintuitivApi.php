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
    public const MODE = [
        'HOME' => 'home',
        'HG' => 'hg',
        'OFF' => 'off',
        'ABSENT' => 'away'
    ];

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     */
    public function getToken(): ResponseInterface
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
    public function getRefreshToken(string $refresh_token): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/oauth2/token',[
            'form_params' => [
                'client_id' => base64_decode(self::CLIENT_ID),
                'client_secret' => base64_decode(self::CLIENT_SECRET),
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getHome(string $token)
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $home =  json_decode($getoauth, true);

        return $home['body']['homes'][0];
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeId(string $token)
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['id'];
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeName(string $token)
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['name'];
    }

    /**
     * @throws GuzzleException
     */
    public function getModeHome(string $token){
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeid = json_decode($getoauth, true);

        return $homeid['body']['homes'][0]['therm_mode'];
    }

    /**
     * @throws GuzzleException
     */
    public function setModeHome(string $modehome,string $token): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/setthermmode',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'mode' => $modehome,
                'home_id' => $this->getHomeId($token)
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeSchedulesAll(string $token): array
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $homeschedules = json_decode($getoauth, true);
        $homeschedules = $homeschedules['body']['homes'][0]['therm_schedules'];

        $allschedule = [];
        foreach ($homeschedules as $value){
            $allschedule[] = $value;
        }
        return $allschedule;
    }

    /**
     * @throws GuzzleException
     */
    public function setSwitchHomeSchedule(string $scheduleid,string $token): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/switchhomeschedule',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'schedule_id' => $scheduleid,
                'home_id' => $this->getHomeId($token)
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getRoomsIdAndName(string $token): array
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
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
    public function getConfigHome(string $token): array
    {
        $reponse = $this->getClient()->request('POST',self::URL.'/syncapi/v1/getconfigs',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'home_id' => $this->getHomeId($token)
            ]
        ]);

        $getconfighome = $reponse->getBody()->getContents();
        $getconfig = json_decode($getconfighome, true);

        return $getconfig['body']['home']['modules'];
    }

    /**
     * @throws GuzzleException
     */
    public function getRooms(string $token){
        $reponse = $this->getClient()->request('POST', self::URL . '/syncapi/v1/homestatus', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ],
            'json' => [
                'home_id' => $this->getHomeId($token)
            ]
        ]);

        $getoauth = $reponse->getBody()->getContents();
        $rooms = json_decode($getoauth, true);

        return $rooms['body']['home']['rooms'];
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomTemperature(string $roomid, float $roomtemp, string $token, int $thermsetpointendtime): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'therm_setpoint_mode' => 'manual',
                            'therm_setpoint_temperature' => $roomtemp,
                            'id' => $roomid,
                            'therm_setpoint_end_time' => $thermsetpointendtime
                        ]
                    ],
                    'id' => $this->getHomeId($token)
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomMode(string $roomid,string $token, string $thermsetpointmode): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'therm_setpoint_mode' => $thermsetpointmode,
                            'id' => $roomid
                        ]
                    ],
                    'id' => $this->getHomeId($token)
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomWindows(string $roomid, bool $windows, string $token): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/setstate',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'home' => [
                    'rooms' => [
                        [
                            'open_window' => $windows,
                            'id' => $roomid
                        ]
                    ],
                    'id' => $this->getHomeId($token)
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