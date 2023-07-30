<?php

class schedules
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSchedules(): array
    {
        $gethomeschedulesall = mullerintuitiv::getHomeSchedulesAll();

        $offset = 1440;
        $planningall = [];
        $i = 0;
        $j = 0;
        $name = '';
        foreach ($gethomeschedulesall as $gethomeschedule){
            if ($gethomeschedule['selected'] === true){
                $planning = $gethomeschedule['timetable'];
                $name = $gethomeschedule['name'];
                $planningall['zones'] = $gethomeschedule['zones'];
                foreach ($planning as $value){
                    $moffset = $value['m_offset'];
                    $zoneid = $value['zone_id'];
                    if ($moffset >= 1 && $moffset <= $offset){
                        $planningall['day']['lundi'][$j]['jour'] = 'Lundi';
                        $planningall['day']['lundi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['lundi'][$j]['plage'][$i]['date'] = date('i:s', $moffset);
                    } elseif ($moffset > $offset && $moffset <= $offset * 2){
                        $planningall['day']['mardi'][$j]['jour'] = 'Mardi';
                        $planningall['day']['mardi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['mardi'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset);
                    } elseif ($moffset > $offset * 2 && $moffset <= $offset * 3){
                        $planningall['day']['mercredi'][$j]['jour'] = 'Mercredi';
                        $planningall['day']['mercredi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['mercredi'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset * 2);
                    } elseif ($moffset > $offset * 3 && $moffset <= $offset * 4){
                        $planningall['day']['jeudi'][$j]['jour'] = 'Jeudi';
                        $planningall['day']['jeudi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['jeudi'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset * 3);
                    } elseif ($moffset > $offset * 4 && $moffset <= $offset * 5){
                        $planningall['day']['vendredi'][$j]['jour'] = 'Vendredi';
                        $planningall['day']['vendredi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['vendredi'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset * 4);
                    } elseif ($moffset > $offset * 5 && $moffset <= $offset * 6){
                        $planningall['day']['samedi'][$j]['jour'] = 'Samedi';
                        $planningall['day']['samedi'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['samedi'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset * 5);
                    } elseif ($moffset > $offset * 6 && $moffset <= $offset * 7){
                        $planningall['day']['dimanche'][$j]['jour'] = 'Dimanche';
                        $planningall['day']['dimanche'][$j]['plage'][$i]['zone'] = $zoneid;
                        $planningall['day']['dimanche'][$j]['plage'][$i]['date'] = date('i:s', $moffset - $offset * 6);
                    }
                    $i++;
                }
            }
        }

        foreach ($planningall['day'] as $days){
            foreach ($days as $day){
                if ($day['jour'] === 'Lundi'){
                    array_unshift($planningall['day']['lundi'], [
                        'jour' => 'Dimanche',
                        'plage' => [end($planningall['day']['dimanche'][0]['plage'])]
                    ]);

                    foreach ($planningall['day']['mardi'][0]['plage'] as $plage){
                        $planningall['day']['lundi'][] = [
                            'jour' => 'Mardi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Mardi'){
                    array_unshift($planningall['day']['mardi'], [
                        'jour' => 'Lundi',
                        'plage' => [end($planningall['day']['lundi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['mercredi'][0]['plage'] as $plage){
                        $planningall['day']['mardi'][] = [
                            'jour' => 'Mercredi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Mercredi'){
                    array_unshift($planningall['day']['mercredi'], [
                        'jour' => 'Mardi',
                        'plage' => [end($planningall['day']['mardi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['jeudi'][0]['plage'] as $plage){
                        $planningall['day']['mercredi'][] = [
                            'jour' => 'Jeudi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Jeudi'){
                    array_unshift($planningall['day']['jeudi'], [
                        'jour' => 'Mercredi',
                        'plage' => [end($planningall['day']['mercredi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['vendredi'][0]['plage'] as $plage){
                        $planningall['day']['jeudi'][] = [
                            'jour' => 'Vendredi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Vendredi'){
                    array_unshift($planningall['day']['vendredi'], [
                        'jour' => 'Jeudi',
                        'plage' => [end($planningall['day']['jeudi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['samedi'][0]['plage'] as $plage){
                        $planningall['day']['vendredi'][] = [
                            'jour' => 'Samedi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Samedi'){
                    array_unshift($planningall['day']['samedi'], [
                        'jour' => 'Vendredi',
                        'plage' => [end($planningall['day']['vendredi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['dimanche'][0]['plage'] as $plage){
                        $planningall['day']['samedi'][] = [
                            'jour' => 'Dimanche',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }

                if ($day['jour'] === 'Dimanche'){
                    array_unshift($planningall['day']['dimanche'], [
                        'jour' => 'Samedi',
                        'plage' => [end($planningall['day']['samedi'][1]['plage'])]
                    ]);

                    foreach ($planningall['day']['lundi'][1]['plage'] as $plage){
                        $planningall['day']['dimanche'][] = [
                            'jour' => 'Lundi',
                            'plage' => [$plage]
                        ];
                        break;
                    }
                }
            }
        }

        return [
            'planningall' => $planningall,
            'name' => $name
        ];
    }
}