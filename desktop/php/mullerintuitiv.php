<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('mullerintuitiv');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
		<div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="synmodules">
                <i class="fas fa-sync-alt"></i>
                <br>
                <span>{{Synchroniser l'installation}}</span>
            </div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
        <legend><i class="icon maison-home63"></i> {{Home(s)}}</legend>
        <?php
            if (count($eqLogics) === 0) {
                echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Home(s) n\'est paramétré, cliquer sur "Synchroniser l\'installation" pour commencer}}</div>';
            } else {
                // Liste des équipements du plugin
                echo '<div class="eqLogicThumbnailContainer">';
                foreach ($eqLogics as $eqLogic) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    if (preg_match('/home/', $eqLogic->getLogicalId())){
                        echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                        echo    '<img src="' . $plugin->getPathImgIcon() . '"/>';
                        echo    '<br>';
                        echo    '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        ?>
		<legend><i class="fas fa-thermometer-full"></i> {{Mes radiateurs}}</legend>
		<?php
            if (count($eqLogics) === 0) {
                echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Radiateur(s) n\'est paramétré, cliquer sur "Synchroniser l\'installation" pour commencer}}</div>';
            } else {
                // Champ de recherche
                echo '<div class="input-group" style="margin:5px;">';
                echo    '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
                echo    '<div class="input-group-btn">';
                echo        '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
                echo        '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
                echo    '</div>';
                echo '</div>';
                // Liste des équipements du plugin
                echo '<div class="eqLogicThumbnailContainer">';
                foreach ($eqLogics as $eqLogic) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    if (!preg_match('/home/', $eqLogic->getLogicalId())){
                        echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                        echo    '<img src="' . $plugin->getPathImgIcon() . '"/>';
                        echo    '<br>';
                        echo    '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
		?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" >{{Objet parent}}</label>
								<div class="col-sm-7">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
                                            $options = '';
                                            foreach ((jeeObject::buildTree(null, false)) as $object) {
                                                $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                            }
                                            echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
								</div>
							</div>
							<div class="form-group">
								<label for="mullerintuitivid" class="col-sm-3 control-label">{{Id}}</label>
								<div class="col-sm-7">
									<input type="text" readonly class="eqLogicAttr form-control-plaintext" id="mullerintuitivid" data-l1key="configuration" data-l2key="mullerintuitiv_id"/>
								</div>
							</div>
                            <div class="form-group">
                                <label for="mullerintuitivtype" class="col-sm-3 control-label">{{Type}}</label>
                                <div class="col-sm-7">
                                    <input type="text" readonly class="eqLogicAttr form-control-plaintext" id="mullerintuitivtype" data-l1key="configuration" data-l2key="mullerintuitiv_type"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="mullerintuitivthermrelay" class="col-sm-3 control-label">{{Adresse mac / Module}}</label>
                                <div class="col-sm-7">
                                    <input type="text" readonly class="eqLogicAttr form-control-plaintext" id="mullerintuitivthermrelay" data-l1key="configuration" data-l2key="mullerintuitiv_therm_relay"/>
                                </div>
                            </div>
						</div>

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<div class="text-center">
									<img name="icon_visu" src="<?= $plugin->getPathImgIcon(); ?>" style="max-width:160px;"/>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
				<hr>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
				<br/><br/>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
                                <th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
                                <th style="min-width:200px;width:350px;">{{Nom}}</th>
                                <th>{{Type}}</th>
                                <th style="min-width:260px;">{{Options}}</th>
                                <th>{{Etat}}</th>
                                <th style="min-width:80px;width:200px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #commandtab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'mullerintuitiv', 'js', 'mullerintuitiv');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
