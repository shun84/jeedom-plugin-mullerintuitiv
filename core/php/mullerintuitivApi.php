<?php

class mullerintuitivApi
{
    protected const URL = 'https://app.muller-intuitiv.net';
    protected const CLIENT_ID = 'NTllNjA0OTQ4ZmUyODNmZDRkYzdlMzU1';
    protected const CLIENT_SECRET = 'ckFlV3U4WTNZcVhFUHFSSjRCcEZ6Rkc5OE1SWHBDY3o=';
    public const MODE = [
        'HOME' => 'home',
        'HG' => 'hg',
        'OFF' => 'off',
        'ABSENT' => 'away'
    ];

    /**
     * @throws Exception
     */
    public function getToken(string $username, string $password)
    {
        $data = http_build_query([
            'client_id' => base64_decode(self::CLIENT_ID),
            'user_prefix' => 'muller',
            'client_secret' => base64_decode(self::CLIENT_SECRET),
            'grant_type' => 'password',
            'scope' => 'read_muller write_muller',
            'password' => $password,
            'username' => $username
        ]);

        $request_http = new com_http(self::URL . '/oauth2/token');
        $request_http->setHeader([
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        $request_http->setPost($data);

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function getRefreshToken(string $refresh_token)
    {
        $data = http_build_query([
            'client_id' => base64_decode(self::CLIENT_ID),
            'client_secret' => base64_decode(self::CLIENT_SECRET),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ]);

        $request_http = new com_http(self::URL . '/oauth2/token');
        $request_http->setHeader([
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        $request_http->setPost($data);

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function getHomes(string $token)
    {
        $request_http = new com_http(self::URL . '/api/homesdata');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function setModeHome(string $modehome, string $token, string $homeid): string
    {
        $data = [
            'mode' => $modehome,
            'home_id' => $homeid
        ];
        $request_http = new com_http(self::URL . '/api/setthermmode');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return $request_http->exec();
    }

    /**
     * @throws Exception
     */
    public function setSwitchHomeSchedule(string $scheduleid, string $token, string $homeid): string
    {
        $data = [
            'schedule_id' => $scheduleid,
            'home_id' => $homeid
        ];
        $request_http = new com_http(self::URL . '/api/switchhomeschedule');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return $request_http->exec();
    }

    /**
     * @throws Exception
     */
    public function getConfigHome(string $token, string $homeid)
    {
        $data = [
            'home_id' => $homeid
        ];
        $request_http = new com_http(self::URL . '/syncapi/v1/getconfigs');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function getRooms(string $token, string $homeid)
    {
        $data = [
            'home_id' => $homeid
        ];
        $request_http = new com_http(self::URL . '/syncapi/v1/homestatus');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function setRoomTemperature(
        string $roomid,
        float  $roomtemp,
        string $token,
        int    $thermsetpointendtime,
        string $homeid
    ): string
    {
        $data = [
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
        ];
        $request_http = new com_http(self::URL . '/syncapi/v1/setstate');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return $request_http->exec();
    }

    /**
     * @throws Exception
     */
    public function setRoomMode(
        string $roomid,
        string $token,
        string $thermsetpointmode,
        string $homeid
    ): string
    {
        $data = [
            'home' => [
                'rooms' => [
                    [
                        'therm_setpoint_mode' => $thermsetpointmode,
                        'id' => $roomid
                    ]
                ],
                'id' => $homeid
            ]
        ];
        $request_http = new com_http(self::URL . '/syncapi/v1/setstate');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return $request_http->exec();
    }

    /**
     * @throws Exception
     */
    public function setRoomWindows(
        string $roomid,
        bool   $windows,
        string $token,
        string $homeid
    ): string
    {
        $data = [
            'home' => [
                'rooms' => [
                    [
                        'open_window' => $windows,
                        'id' => $roomid
                    ]
                ],
                'id' => $homeid
            ]
        ];
        $request_http = new com_http(self::URL . '/syncapi/v1/setstate');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return $request_http->exec();
    }

    /**
     * @throws Exception
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
        $data = [
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
        ];
        $request_http = new com_http(self::URL . '/api/gethomemeasure');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return json_decode($request_http->exec(), true);
    }

    /**
     * @throws Exception
     */
    public function getHomeMeasure(
        string $modulesid,
        string $token,
        string $dateend,
        string $datebegin,
        string $scale,
        string $homeid
    )
    {
        $data = [
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
        ];
        $request_http = new com_http(self::URL . '/api/gethomemeasure');
        $request_http->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        $request_http->setPost(json_encode($data));

        return json_decode($request_http->exec(), true);
    }
}