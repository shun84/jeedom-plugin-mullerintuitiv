<?php

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class rooms
{
    /**
     * @throws GuzzleException
     */
    public function getRooms(string $token, string $homeid){
        $mullerintuitivApi = new mullerintuitivApi();
        $reponse = $mullerintuitivApi->getRooms($token, $homeid);

        $getoauth = $reponse->getBody()->getContents();
        $rooms = json_decode($getoauth, true);

        return $rooms['body']['home']['rooms'];
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
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomMode($roomid, $token, $thermsetpointmode, $homeid);
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
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomTemperature($roomid, $roomtemp, $token, $thermsetpointendtime, $homeid);
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
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomWindows($roomid, $windows, $token, $homeid);
    }
}