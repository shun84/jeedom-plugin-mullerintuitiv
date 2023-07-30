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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <div class="form-group">
        <label for="login"  class="col-md-4 control-label">{{Login}}</label>
        <div class="col-md-2">
            <input class="configKey form-control" id="login" data-l1key="login"/>
        </div>
    </div>
    <div class="form-group">
        <label for="mdp" class="col-md-4 control-label">{{Mot de passe}}</label>
        <div class="input-group col-md-2">
            <input type="text" class="inputPassword configKey form-control" id="mdp"  data-l1key="mdp"/>
            <span class="input-group-btn">
                <a class="btn btn-default form-control bt_showPass roundedRight"><i class="fas fa-eye"></i></a>
            </span>
        </div>
    </div>
</form>
