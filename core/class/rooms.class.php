<?php

class rooms
{
    /**
     * @throws Exception
     */
    public static function getRooms(string $homeid){
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();
        $rooms = $mullerintuitivApi->getRooms($token, $homeid);

        return $rooms['body']['home']['rooms'];
    }

    /**
     * @throws Exception
     */
    public static function setRoomMode(
        string $roomid,
        string $thermsetpointmode,
        string $homeid
    ): string
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomMode($roomid, $token, $thermsetpointmode, $homeid);
    }

    /**
     * @throws Exception
     */
    public static function setRoomTemperature(
        string $roomid,
        float $roomtemp,
        int $thermsetpointendtime,
        string $homeid
    ): string
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomTemperature($roomid, $roomtemp, $token, $thermsetpointendtime, $homeid);
    }

    /**
     * @throws Exception
     */
    public static function setRoomWindows(
        string $roomid,
        bool $windows,
        string $homeid
    ): string
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setRoomWindows($roomid, $windows, $token, $homeid);
    }
}