<?php

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class homes
{
    /**
     * @throws GuzzleException
     */
    public function getHomes(string $token){
        $mullerintuitivApi = new mullerintuitivApi();
        $gethomes = $mullerintuitivApi->getHomes($token);

        $getoauth = $gethomes->getBody()->getContents();
        $home =  json_decode($getoauth, true);

        return $home['body']['homes'];
    }

    /**
     * @throws GuzzleException
     */
    public function getRoomsIdAndName(string $token): array
    {
        $idandname = [];
        $homes = $this->getHomes($token);

        foreach ($homes as $home){
            foreach ($home['rooms'] as $value){
                $idandname[] = $value;
            }
        }

        return $idandname;
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeSchedulesAll(string $token): array
    {
        $allschedule = [];
        $homes = $this->getHomes($token);

        foreach ($homes as $home){
            foreach ($home['therm_schedules'] as $value){
                $allschedule[] = $value;
            }
        }

        return $allschedule;
    }

    /**
     * @throws GuzzleException
     */
    public function getConfigHome(string $token, string $homeid): array
    {
        $mullerintuitivApi = new mullerintuitivApi();
        $reponse = $mullerintuitivApi->getConfigHome($token, $homeid);

        $getconfighome = $reponse->getBody()->getContents();
        $getconfig = json_decode($getconfighome, true);

        return $getconfig['body']['home']['modules'];
    }

    /**
     * @throws GuzzleException
     */
    public function setModeHome(string $modehome, string $token, string $homeid): ResponseInterface
    {
        $mullerintuitivApi = new mullerintuitivApi();
         return $mullerintuitivApi->setModeHome(
            $modehome,
            $token,
            $homeid
        );
    }

    /**
     * @throws GuzzleException
     */
    public function setSwitchHomeSchedule(string $scheduleid,string $token, string $homeid): ResponseInterface
    {
        $mullerintuitivApi = new mullerintuitivApi();

        return $mullerintuitivApi->setSwitchHomeSchedule($scheduleid, $token, $homeid);
    }
}