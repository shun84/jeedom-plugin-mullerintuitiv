<?php

use GuzzleHttp\Exception\GuzzleException;

class measure
{

    /**
     * @throws GuzzleException
     */
    public function getRoomMeasures(
        string $dateend,
        string $datebegin,
        string $roomid,
        string $bridge,
        string $homeid,
        string $token
    ): array
    {
        $getdayroommeasures = $this->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1day',
            $bridge,
            $homeid
        );

        $getweekroommeasures = $this->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1week',
            $bridge,
            $homeid
        );

        $getmonthroommeasures = $this->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            '1month',
            $bridge,
            $homeid
        );

        return $this->getAllMeasures(
            $getdayroommeasures,
            $getweekroommeasures,
            $getmonthroommeasures
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getHomeMeasures(
        string $modulesid,
        string $dateend,
        string $datebegin,
        string $homeid,
        string $token
    ): array
    {
        $getdayhomemeasures = $this->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1day',
            $homeid
        );

        $getweekhomemeasures = $this->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1week',
            $homeid
        );

        $getmonthhomemeasures = $this->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            '1month',
            $homeid
        );

        return $this->getAllMeasures(
            $getdayhomemeasures,
            $getweekhomemeasures,
            $getmonthhomemeasures
        );
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
    ){
        $mullerintuitivApi = new mullerintuitivApi();

        $gethomemeasure = $mullerintuitivApi->getHomeMeasure(
            $modulesid,
            $token,
            $dateend,
            $datebegin,
            $scale,
            $homeid
        );

        $getoauth = $gethomemeasure->getBody()->getContents();
        $rooms = json_decode($getoauth, true);

        return $rooms['body']['home']['modules'][0]['measures'];
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
    ){
        $mullerintuitivApi = new mullerintuitivApi();

        $getroommeasure = $mullerintuitivApi->getRoomMeasure(
            $roomid,
            $token,
            $dateend,
            $datebegin,
            $scale,
            $bridge,
            $homeid
        );

        $getoauth = $getroommeasure->getBody()->getContents();
        $rooms = json_decode($getoauth, true);

        return $rooms['body']['home']['rooms'][0]['measures'];
    }

    public function getAllMeasures(
        array $getdayroommeasures,
        array $getweekroommeasures,
        array $getmonthroommeasures
    ): array
    {
        $valueanddate = [
            'day' => [],
            'week' => [],
            'month' => []
        ];

        foreach ($getdayroommeasures as $getdayroommeasure){
            $begtime = $getdayroommeasure['beg_time'];
            $begtime = strtotime('-11 hour', $begtime);
            $values = $getdayroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['day'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 day', $begtime);
            }
        }

        foreach ($getweekroommeasures as $getweekroommeasure){
            $begtime = $getweekroommeasure['beg_time'];
            $begtime = strtotime('-3 day -11 hour', $begtime);
            $values = $getweekroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['week'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 week', $begtime);
            }
        }

        foreach ($getmonthroommeasures as $getmonthroommeasure){
            $begtime = $getmonthroommeasure['beg_time'];
            $mois = date('m',$begtime);
            $annee = date('Y', $begtime);
            $begtime = strtotime($annee.'-'.$mois.'-01 00:00:00 UTC');
            $values = $getmonthroommeasure['value'];
            foreach ($values as $value){
                $val = $value[0];
                $valueanddate['month'][] = [
                    $begtime * 1000,
                    $val / 1000
                ];
                $begtime = strtotime('+1 month', $begtime);
            }
        }

        return $valueanddate;
    }
}