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
<form>
    <div class="form-group">
        <label for="login">{{Login}}</label>
        <input style="width: 300px" class="configKey form-control" id="login" data-l1key="login"/>
    </div>
    <div class="form-group">
        <label for="mdp">{{Mot de passe}}</label>
        <input type="password" style="width: 300px" class="configKey form-control" id="mdp" data-l1key="mdp"/>
    </div>
</form>
