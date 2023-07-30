<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

use GuzzleHttp\Exception\GuzzleException;

if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

try {
    $getschedules = mullerintuitiv::getSchedules();
} catch (GuzzleException $e) {
    ajax::error(displayException($e), $e->getCode());
}
?>

<div class="planning" data-planning="<?php echo $getschedules['name'] ?>">
    <ul class="nav nav-pills">
        <li class="active"><a style="border-radius: 5px;" data-toggle="pill" href="#lundi">{{LUN.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#mardi">{{MAR.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#mercredi">{{MER.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#jeudi">{{JEU.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#vendredi">{{VEN.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#samedi">{{SAM.}}</a></li>
        <li><a style="border-radius: 5px" data-toggle="pill" href="#dimanche">{{DIM.}}</a></li>
    </ul>

    <div id="schedule" class="tab-content">
        <div id="lundi" class="tab-pane fade in active">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['lundi'] as $day){
                        foreach ($day['plage'] as $lundi){
                            if ($day['jour'] === 'Dimanche'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{DIM. ' . $lundi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Mardi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{MAR. ' . $lundi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $lundi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $lundi['zone'] && $day['jour'] !== 'Mardi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="mardi" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['mardi'] as $day){
                        foreach ($day['plage'] as $mardi){
                            if ($day['jour'] === 'Lundi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{LUN. ' . $mardi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Mercredi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{MER. ' . $mardi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $mardi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $mardi['zone'] && $day['jour'] !== 'Mercredi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="mercredi" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['mercredi'] as $day){
                        foreach ($day['plage'] as $mercredi){
                            if ($day['jour'] === 'Mardi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{MAR. ' . $mercredi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Jeudi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{JEU. ' . $mercredi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $mercredi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $mercredi['zone'] && $day['jour'] !== 'Jeudi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="jeudi" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['jeudi'] as $day){
                        foreach ($day['plage'] as $jeudi){
                            if ($day['jour'] === 'Mercredi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{MER. ' . $jeudi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Vendredi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{VEN. ' . $jeudi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $jeudi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $jeudi['zone'] && $day['jour'] !== 'Vendredi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="vendredi" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['vendredi'] as $day){
                        foreach ($day['plage'] as $vendredi){
                            if ($day['jour'] === 'Jeudi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{JEU. ' . $vendredi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Samedi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{SAM. ' . $vendredi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $vendredi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $vendredi['zone'] && $day['jour'] !== 'Samedi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="samedi" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['samedi'] as $day){
                        foreach ($day['plage'] as $samedi){
                            if ($day['jour'] === 'Vendredi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{VEN. ' . $samedi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Dimanche'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{DIM. ' . $samedi['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $samedi['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $samedi['zone'] && $day['jour'] !== 'Dimanche'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
        <div id="dimanche" class="tab-pane fade">
            <ul class="list-group">
                <?php
                    foreach ($getschedules['planningall']['day']['dimanche'] as $day){
                        foreach ($day['plage'] as $dimanche){
                            if ($day['jour'] === 'Samedi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{SAM. ' . $dimanche['date'] . '}}<div class="lignegreyday"></div></li>';
                            } elseif ($day['jour'] === 'Lundi'){
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{LUN. ' . $dimanche['date'] . '}}<div class="lignegreyday"></div></li>';
                            } else {
                                echo '<li class="list-group-item" style="margin-bottom: 5px;">{{' . $dimanche['date'] . '}}<div class="lignegrey"></div></li>';
                            }

                            foreach ($getschedules['planningall']['zones'] as $zone){
                                if ($zone['id'] === $dimanche['zone'] && $day['jour'] !== 'Lundi'){
                                    if ($zone['name'] === 'À la maison'){
                                        echo '<li style="background-color: rgb(241,155,100) !important" class="list-group-item planninglist"><i class="fas fa-home"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Nuit') {
                                        echo '<li style="background-color: rgb(116,190,249) !important" class="list-group-item planninglist"><i class="far fa-moon"></i> '.$zone['name'].'</li>';
                                    } elseif ($zone['name'] === 'Économie') {
                                        echo '<li style="background-color: rgb(98,195,137) !important" class="list-group-item planninglist"><i class="fab fa-envira"></i> '.$zone['name'].'</li>';
                                    } else {
                                        echo '<li style="background-color: rgb(85,187,191) !important" class="list-group-item planninglist"><i class="fas fa-bullseye"></i> '.$zone['name'].'</li>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </ul>
        </div>
    </div>
</div>
<style>
    .planninglist {
        display: table-cell;
        border-radius: 10px;
        width: 350px;
        height: 40px;
        left: 50px;
        color: white;
        vertical-align: middle;
    }

    .lignegrey {
        border-bottom: grey solid;
        display:inline-block;
        width: 85%;
        margin-left: 10px;
    }

    .lignegreyday {
        border-bottom: grey solid;
        display:inline-block;
        width: 77%;
        margin-left: 10px;
    }

    .nav-pills>li.active>a {
        color: white !important;
        background-color: rgb(241,155,100) !important;
    }

    .ui-widget-content {
        width: 500px !important;
        height: 465px !important;
    }
</style>
<script>
    if (document.querySelector('.title')){
        document.querySelector('.title').innerText = 'Planning - Actif -> '+ document.querySelector('.planning').dataset.planning
    }
</script>


