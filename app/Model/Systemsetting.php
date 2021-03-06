<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

class Systemsetting extends AppModel{
	
	public function findNice(){
		$systemsettings = $this->find('all');
		$all_systemsettings = [];
		
		$all_systemsettings['WEBSERVER']   = Hash::extract($systemsettings, '{n}.Systemsetting[section=WEBSERVER]');
		$all_systemsettings['SUDO_SERVER'] = Hash::extract($systemsettings, '{n}.Systemsetting[section=SUDO_SERVER]');
		$all_systemsettings['MONITORING']  = Hash::extract($systemsettings, '{n}.Systemsetting[section=MONITORING]');
		//$all_systemsettings['CRONJOB']     = Hash::extract($systemsettings, '{n}.Systemsetting[section=CRONJOB]');
		$all_systemsettings['SYSTEM']      = Hash::extract($systemsettings, '{n}.Systemsetting[section=SYSTEM]');
		$all_systemsettings['FRONTEND']    = Hash::extract($systemsettings, '{n}.Systemsetting[section=FRONTEND]');
		$all_systemsettings['CHECK_MK']    = Hash::extract($systemsettings, '{n}.Systemsetting[section=CHECK_MK]');
		$all_systemsettings['ARCHIVE']    = Hash::extract($systemsettings, '{n}.Systemsetting[section=ARCHIVE]');
		$all_systemsettings['TICKET_SYSTEM']    = Hash::extract($systemsettings, '{n}.Systemsetting[section=TICKET_SYSTEM]');
		
		return $all_systemsettings;
	}
	
	public function findAsArray(){
		$return = [];
		$systemsettings = $this->findNice();
		
		foreach($systemsettings as $key => $values){
			$return[$key] = [];
			foreach($values as $value){
				$return[$key][$value['key']] = $value['value'];
			}
		}
		
		return $return;
	}
	
	public function findAsArraySection($section = ''){
		$return = [];
		$systemsettings = $this->findAllBySection($section);

		$all_systemsettings = [];
		$all_systemsettings[$section] = Hash::extract($systemsettings, '{n}.Systemsetting[section='.$section.']');
		
		foreach($all_systemsettings as $key => $values){
			$return[$key] = [];
			foreach($values as $value){
				$return[$key][$value['key']] = $value['value'];
			}
		}
		return $return;
	}
}