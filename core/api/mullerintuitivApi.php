<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class mullerintuitivApi
{
    protected const URL = 'https://app.muller-intuitiv.net';
    protected const CLIENT_ID = 'NTllNjA0OTQ4ZmUyODNmZDRkYzdlMzU1';
    protected const CLIENT_SECRET = 'ckFlV3U4WTNZcVhFUHFSSjRCcEZ6Rkc5OE1SWHBDY3o=';
    private $client;
    public const MODE = [
        'HOME' => 'home',
        'HG' => 'hg',
        'OFF' => 'off',
        'ABSENT' => 'away'
    ];

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     */
    public function getToken(string $username, string $password): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/oauth2/token',[
            'form_params' => [
                'client_id' => base64_decode(self::CLIENT_ID),
                'user_prefix' => 'muller',
                'client_secret' => base64_decode(self::CLIENT_SECRET),
                'grant_type' => 'password',
                'scope' => 'read_muller write_muller',
                'password' => $password,
                'username' => $username
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
    public function getHomes(string $token): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/homesdata',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setModeHome(string $modehome, string $token, string $homeid): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/setthermmode',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'mode' => $modehome,
                'home_id' => $homeid
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setSwitchHomeSchedule(string $scheduleid,string $token, string $homeid): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/switchhomeschedule',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'schedule_id' => $scheduleid,
                'home_id' => $homeid
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getConfigHome(string $token, string $homeid): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/syncapi/v1/getconfigs',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'home_id' => $homeid
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getRooms(string $token, string $homeid): ResponseInterface
    {
        return $this->getClient()->request('POST', self::URL . '/syncapi/v1/homestatus', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ],
            'json' => [
                'home_id' => $homeid
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomTemperature(
        string $roomid,
        float $roomtemp,
        string $token,
        int $thermsetpointendtime,
        string $homeid
    ): ResponseInterface
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
                    'id' => $homeid
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomMode(
        string $roomid,
        string $token,
        string $thermsetpointmode,
        string $homeid
    ): ResponseInterface
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
                    'id' => $homeid
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function setRoomWindows(
        string $roomid,
        bool $windows,
        string $token,
        string $homeid
    ): ResponseInterface
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
                    'id' => $homeid
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getRoomMeasure(
        string $roomid,
        string $token,
        string $dateend,
        string $datebegin,
        string $scale,
        string $bridge,
        string $homeid
    )
    {
        return $this->getClient()->request('POST',self::URL.'/api/gethomemeasure',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'date_end' => $dateend,
                'date_begin' => $datebegin,
                'scale' => $scale,
                'home' => [
                    'rooms' => [
                        [
                            'type' => 'sum_energy_elec_heating',
                            'id' => $roomid,
                            'bridge' => $bridge
                        ]
                    ],
                    'id' => $homeid
                ]
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeMeasure(
        string $modulesid,
        string $token,
        string $dateend,
        string $datebegin,
        string $scale,
        string $homeid
    ): ResponseInterface
    {
        return $this->getClient()->request('POST',self::URL.'/api/gethomemeasure',[
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json'
            ],
            'json' => [
                'date_end' => $dateend,
                'date_begin' => $datebegin,
                'scale' => $scale,
                'home' => [
                    'modules' => [
                        [
                            'type' => 'sum_energy_elec_heating',
                            'id' => $modulesid
                        ]
                    ],
                    'id' => $homeid
                ]
            ]
        ]);
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}