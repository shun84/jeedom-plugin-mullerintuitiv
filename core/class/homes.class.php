<?php

class homes
{
    /**
     * @throws Exception
     */
    public static function getHomes(){
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();
        $gethomes = $mullerintuitivApi->getHomes($token);

        return $gethomes['body']['homes'];
    }

    /**
     * @throws Exception
     */
    public static function getRoomsIdAndName(): array
    {
        $idandname = [];
        $homes = homes::getHomes();

        foreach ($homes as $home){
            foreach ($home['rooms'] as $value){
                $idandname[] = $value;
            }
        }

        return $idandname;
    }

    /**
     * @throws Exception
     */
    public static function getHomeSchedulesAll(): array
    {
        $allschedule = [];
        $homes = homes::getHomes();

        foreach ($homes as $home){
            foreach ($home['therm_schedules'] as $value){
                $allschedule[] = $value;
            }
        }

        return $allschedule;
    }

    /**
     * @throws Exception
     */
    public static function getConfigHome(string $homeid)
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();
        $getconfig = $mullerintuitivApi->getConfigHome($token, $homeid);

        return $getconfig['body']['home']['modules'];
    }

    /**
     * @throws Exception
     */
    public static function setModeHome(string $modehome, string $homeid): string
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setModeHome(
            $modehome,
            $token,
            $homeid
        );
    }

    /**
     * @throws Exception
     */
    public static function setSwitchHomeSchedule(string $scheduleid, string $homeid): string
    {
        $token = token::getAccesToken();
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setSwitchHomeSchedule($scheduleid, $token, $homeid);
    }
}