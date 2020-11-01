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

if (!isConnect('admin')) {
	throw new Exception('401 - Accès non autorisé');
}
$eqLogics = Freebox_OS::byType('Freebox_OS');
?>

<table class="table table-condensed tablesorter" id="table_healthFreebox_OS">
	<thead>
		<tr>
			<th></th>
			<th>{{Equipement}}</th>
			<th>{{ID}}</th>
			<th>{{logicalId}}</th>
			<th>{{Type d'équipement}}</th>
			<th>{{Type d'action}}</th>
			<th>{{Statut}}</th>
			<th>{{Batterie}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($eqLogics as $eqLogic) {
			$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
			if (file_exists(dirname(__FILE__) . '/../../core/images/' . $eqLogic->getConfiguration('type') . '.png')) {
				$image = '<img src="plugins/Freebox_OS/core/images/' . $eqLogic->getConfiguration('type') . '.png' . '" height="35" width="35" style="' . $opacity . '"/>';
			} else {
				if ($eqLogic->getConfiguration('type') == 'parental' || $eqLogic->getConfiguration('type') == 'player' || $eqLogic->getConfiguration('type') == 'alarm_control' || $eqLogic->getConfiguration('type') == 'alarm_sensor' || $eqLogic->getConfiguration('type') == 'alarm_remote') {
					$template = $eqLogic->getConfiguration('type');
					$icon = $template;
				} else {
					$template = $eqLogic->getLogicalId();
					if (($eqLogic->getConfiguration('type') == 'info' && $eqLogic->getConfiguration('action') == 'store') || ($eqLogic->getConfiguration('type') == 'light')) {
						$icon = 'default';
					} else {
						$icon = $template;
					}
				}
				$image = '<img src="plugins/Freebox_OS/core/images/' . $icon . '.png" height="35" width="35" style="' . $opacity . '" class="' . $opacity . '"/>';
			}
			echo '<tr><td class="' . $opacity . '" >' . $image . '</td><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('logicalID') . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('type') . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('action') . '</span></td>';

			$status = '<span class="label label-success" style="font-size : 1em;cursor:default;">{{OK}}</span>';
			if ($eqLogic->getStatus('state') == 'nok') {
				$status = '<span class="label label-danger" style="font-size : 1em;cursor:default;">{{NOK}}</span>';
			}
			echo '<td>' . $status . '</td>';
			$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
			$battery = $eqLogic->getStatus('battery');
			if ($eqLogic->getConfiguration('type') == 'alarm_sensor' && $battery == '') {
				$battery = 'N/A';
			}
			if ($battery == '') {
				$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fas fa-plug"></i></span>';
			} elseif ($battery < 20 && $battery != 'N/A') {
				$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
			} elseif ($battery < 60) {
				$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
			} elseif ($battery > 60) {
				$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
			} elseif ($battery == 'N/A') {
				$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
			} else {
				$battery_status = '<span class="label label-primary" style="font-size : 1em;">' . $battery . '%</span>';
			}
			echo '<td>' . $battery_status . '</td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td>';
		}
		?>
	</tbody>
</table>