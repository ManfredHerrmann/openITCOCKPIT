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
App::import('Model', 'Command');
App::import('Model', 'Contactgroup');
App::import('Model', 'Contact');
App::import('Model', 'Hostdependency');
App::import('Model', 'Hostescalation');
App::import('Model', 'Hostgroup');
App::import('Model', 'Host');
App::import('Model', 'Hosttemplate');
App::import('Model', 'Servicedependency');
App::import('Model', 'Serviceescalation');
App::import('Model', 'Servicegroup');
App::import('Model', 'Service');
App::import('Model', 'Servicetemplate');
App::import('Model', 'Timeperiod');
App::import('Model', 'Commandargument');
App::import('Model', 'Container');
class NagiosImportShell extends AppShell {
    /* @var object Object Instance for access to specific object functions */
    private $mainObj = null;
    private $commands_configutation = array();
    private $contacts_configutation = array();
    private $contactgroups_configutation = array();
    private $hostdependencies_configutation = array();
    private $hostescalations_configutation = array();
    private $hosts_configutation = array();
    private $hosttemplates_configutation = array();
    private $hostgroups_configutation = array();
    private $servicedependencies_configutation = array();
    private $servicescalations_configutation = array();
    private $services_configutation = array();
    private $servicetemplates_configutation = array();
    private $servicegroups_configutation = array();
    private $timeperiods_configutation = array();
    public function main(){
        $start = null;
        $end = null;

        $this->out('### Welcome to the nagios configuration import ###');
        //Choosing the Backup to import
        $backup_dir = $this->chooseBackupDir();
        //Check if the correct paramter for backupdirectory is choosen.
        if ($backup_dir == null) {
            $this->out("<error>Please choose correct Backupdirectory</error>");
        } else {
            //Choosing the importmode
            $import_mode = $this->chooseImportMode();
            switch ($import_mode){
                //Clear database and import backup or import to a blank database
                case 1:
                    $start = time();
                    //$this->clearDatabase();
                    $this->parseDir($backup_dir, true);
                    foreach($this->configutationArray['Command'] as $configuration) {
                        $command = new Command();
                        $command->saveAll($configuration);
                    }
                    $end = time();
                    break;
                //Keep the Database, override existing Entries and recreate deleted Entries
                case 2:
                    $start = time();
                    $this->parseDir($backup_dir, true);
                    $end = time();
                    break;
                //Keep the Database, do not overriede existing Entries and recreate deleted Entries
                case 3:
                    $start = time();
                    $this->parseDir($backup_dir, false);
                    $end = time();
                    break;
                default:
                    $this->out("<error>Please choose correct Importmode</error>");
                    break;
            }

            if ($start != null && $end != null) {
                $this->out("Duration of Import:".$this->makeDifferenz($start,$end));
                $this->out('### End of importing Configuration ###');
            }
        }
    }

    public function chooseBackupDir() {
        Configure::load('nagios');
        $this->conf = Configure::read('nagios.export');
        $this->out('Please choose a backup-directory to load the config-files:');

        $all_files = scandir($this->conf['backupTarget']);
        $count = 1;
        $backup_dirs = array();
        foreach ($all_files as $file) {
            if($file != '.' && $file != '..' && !strstr($file,'.')) {
                $this->out($count.". ".$file);
                $backup_dirs[$count] = $this->conf['backupTarget']."/".$file;
                $count++;
            }

        };
        $import_dir = $this->in("Enter the number of the backup-directory you wish to import:");
        if ($import_dir > 0 && $import_dir <= sizeof($backup_dirs)){
            return $backup_dirs[$import_dir];
        } else {
            return null;
        }
    }

    public function chooseImportMode(){
        $this->out('Please choose an Importmode:');
        $this->out('1. Clear database and import backup or import to a blank database');
        $this->out('2. Keep the Database, override existing Entries and recreate deleted Entries');
        $this->out('3. Keep the Database, do not overriede existing Entries and recreate deleted Entries');
        return $this->in("Enter the number of your favourite importmode:");
    }

    public function parseDir($path, $override) {
        $all_files = scandir($path);
        foreach ($all_files as $file) {
            if (is_dir($path."/".$file) && $file != "." && $file != "..") {
                $config_files = scandir($path."/".$file);
                $this->out("<info>Processing configurations for ".$file."</info>");
                if (sizeof($config_files) == 2) {
                    $this->out("<warning>No Configfile for ".$file." defined</warning>");
                }
                foreach ($config_files as $config_file) {
                    if (!is_dir($config_file)) {
                        $this->parseFile($path."/".$file."/".$config_file, $override);
                    }
                }
            }
        }
    }

    private function clearDatabase() {
        //Contacts trunken und wieder einspielen -> kein Problem
        $contact = new Contact();
        $contact->query('TRUNCATE TABLE contacts;');
        unset($contact);
        //Contactgroups trunken und wieder einspielen -> kein Problem
        $contactgroup = new Contactgroup();
        $contactgroup->query('TRUNCATE TABLE contactgroups;');
        unset($contactgroup);
        //Commands trunken und wieder einspielen -> kein Problem
        $command = new Command();
        $command->query('TRUNCATE TABLE commands;');
        unset($command);
        //Hostdependencies trunken und wieder einspielen -> keine Config zum testen da
        $hostdependecy = new Hostdependency();
        $hostdependecy->query('TRUNCATE TABLE hostdependencies;');
        unset($hostdependecy);
        //Hostescalations trunken und wieder einspielen -> kein Problem
        $hostescalation = new Hostescalation();
        $hostescalation->query('TRUNCATE TABLE hostescalations;');
        unset($hostescalation);
        //Hostgroups trunken und wieder einspielen -> kein Problem
        $hostgroup = new Hostgroup();
        $hostgroup->query('TRUNCATE TABLE hostgroups;');
        unset($hostgroup);
        //Hosts trunken und wieder einspielen -> kein Problem
        $host = new Host();
        $host->query('TRUNCATE TABLE hosts;');
        unset($host);
        //Hosttemplates trunken und wieder einspielen -> kein Problem
        $hosttemplate = new Hosttemplate();
        $hosttemplate->query('TRUNCATE TABLE hosttemplates;');
        unset($hosttemplate);
        //Servicedependencies trunken und wieder einspielen -> kein Problem
        $servicedependency = new Servicedependency();
        $servicedependency->query('TRUNCATE TABLE servicedependencies;');
        unset($servicedependency);
        //Serviceescalations trunken und wieder einspielen -> kein Problem
        $serviceescalation = new Serviceescalation();
        $serviceescalation->query('TRUNCATE TABLE serviceescalations;');
        unset($serviceescalation);
        //Servicegroups trunken und wieder einspielen -> kein Problem
        $servicegroup = new Servicegroup();
        $servicegroup->query('TRUNCATE TABLE servicegroups;');
        unset($servicegroup);
        //Services trunken und wieder einspielen -> kein Problem
        $service = new Service();
        $service->query('TRUNCATE TABLE services;');
        unset($service);
        //Servicetemplates trunken und wieder einspielen -> kein Problem
        $servicetemplate = new Servicetemplate();
        $servicetemplate->query('TRUNCATE TABLE servicetemplates;');
        unset($servicetemplate);
        //Timeperiods und Timeperiod_Timeranges trunken und wieder einspielen -> kein Problem
        $timeperiod = new Timeperiod();
        $timeperiod->query('TRUNCATE TABLE timeperiods;');
        unset($timeperiod);
        $timerange = new Timerange();
        $timerange->query('TRUNCATE TABLE timeperiod_timeranges;');
        unset($timerange);
    }
    public function parseFile($file, $override) {
        //Check if the given File is a valid config file with the right suffix
        if (strstr($file,$this->conf["suffix"])) {
            $file_parts = explode("/",$file);
            //Get the config type from the configfilename
            $config_type = $file_parts[sizeof($file_parts)-2];
            $config_name = explode(".", $file_parts[sizeof($file_parts)-1]);
            //Read the whole config file into an array named cfg_file
            $cfg_file = file($file);
            switch($config_type){
                case "commands":
                    //Read the config file and check if it was empty
                    if (!$this->readCommandConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Command defined</warning>");
                    }
                    break;
                case "contactgroups":
                    //Read the config file and check if it was empty
                    if (!$this->readContactgroupConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Contactgroup defined</warning>");
                    }
                    break;
                case "contacts":
                    //Read the config file and check if it was empty
                    if (!$this->readContactsConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Contact defined</warning>");
                    }
                    break;
                case "hostdependencies":
                    //Read the config file and check if it was empty
                    if (!$this->readHostdependencyConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Hostdependencie defined</warning>");
                    }
                    break;
                case "hostescalations":
                    //Read the config file and check if it was empty
                    if (!$this->readHostescalationConfigFile($cfg_file, false, $override, $config_name[0])) {
                        $this->out("<warning>No Hostescalation defined</warning>");
                    }
                    break;
                case "hostgroups":
                    //Read the config file and check if it was empty
                    if (!$this->readHostgroupConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Hostgroup defined</warning>");
                    }
                    break;
                case "hosts":
                    //Read the config file and check if it was empty
                    if (!$this->readHostConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Host defined</warning>");
                    }
                    break;
                case "hosttemplates":
                    //Read the config file and check if it was empty
                    if (!$this->readHosttemplateConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Hosttemplate defined</warning>");
                    }
                    break;
                case "servicedependencies":
                    //Read the config file and check if it was empty
                    if (!$this->readServicedependencyConfigFile($cfg_file, false, $override, $config_name[0])) {
                        $this->out("<warning>No Service-Dependencies defined</warning>");
                    }
                    break;
                case "serviceescalations":
                    //Read the config file and check if it was empty
                    if (!$this->readServiceescalationConfigFile($cfg_file, false, $override, $config_name[0])) {
                        $this->out("<warning>No Service-Escalation defined</warning>");
                    }
                    break;
                case "servicegroups":
                    //Read the config file and check if it was empty
                    if (!$this->readServicegroupConfigFile($cfg_file, false, $override, $config_name[0])) {
                        $this->out("<warning>No Service-Group defined</warning>");
                    }
                    break;
                case "services":
                    //Read the config file and check if it was empty
                    if (!$this->readServiceConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Service defined</warning>");
                    }
                    break;
                case "servicetemplates":
                    //Read the config file and check if it was empty
                    if (!$this->readServicetemplateConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Service-Template defined</warning>");
                    }
                    break;
                case "timeperiods":
                    //Read the config file and check if it was empty
                    if (!$this->readTimeperiodConfigFile($cfg_file, false, $override)) {
                        $this->out("<warning>No Service-Group defined</warning>");
                    }
                    break;
                case "defaults":
                    //@TODO: Müssen die Default Configs auch in die Datenbank (scheinen nicht abgelegt zu sein)?
                    //$this->out("Defaults");
                    break;
            }
        } else {
            $this->out("<error>!!! ".$file." is not a valid Config File or suffix is wrong !!!</error>");
        }
    }

    private function readCommandConfigFile($cfg_file, $newObj = false, $override){
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define command{":
                    $newObj = true;
                    $this->mainObj = new Command();
                    $saveDataArray = array();
                    break;
                case "command_name":
                    $erg = $this->findIDfromUUID($this->mainObj, "Command", $line_parts[1]);
                    if ($erg != null) {
                        $this->mainObj->id = $erg;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Command']['id'] = $erg;
                        $cmd_arg = new Commandargument();
                        $cmd_args = $cmd_arg->findAllByCommandId($erg);
                        $args_array = array();
                        $cnt = 0;
                        foreach($cmd_args as $arg) {
                            $args_array[$cnt]['id'] = $arg['Commandargument']['id'];
                            $args_array[$cnt]['command_id'] = $arg['Commandargument']['command_id'];
                            $args_array[$cnt]['name'] = $arg['Commandargument']['name'];
                            $args_array[$cnt]['human_name'] = $arg['Commandargument']['human_name'];
                            $cnt++;
                        }
                        $saveDataArray['Command']['command_type'] = $this->mainObj->field('command_type');
                        $saveDataArray['Command']['name'] = $this->mainObj->field('name');
                        $saveDataArray['Command']['description'] = $this->mainObj->field('description');
                        $saveDataArray['Commandargument'] = $args_array;
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Command']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Command']['name'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Command']['command_type'] = '1';
                        $saveDataArray['Command']['description'] = "Imported from Backup. Description must be added.";
                    }
                    break;
                case "command_line":
                    $saveDataArray['Command']['command_line'] = trim($line_parts[1]);
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->commands_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $saveDataArray = null;
                    $container = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readContactgroupConfigFile($cfg_file, $newObj = false, $override) {
        $contacts = null;
        $saveDataArray = null;
        $container = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define contactgroup{":
                    $newObj = true;
                    $saveDataArray = array();
                    $contacts = array();
                    $this->mainObj = new Contactgroup();
                    break;
                case "contactgroup_name":
                    $erg = $this->findIDfromUUID($this->mainObj, "Contactgroup", $line_parts[1]);
                    if ( $erg != null) {
                        $this->mainObj->id = $erg;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Contactgroup']['id'] = $erg;
                        $saveDataArray['Contactgroup']['container_id'] = $this->mainObj->field('container_id');
                        $saveDataArray['Container']['id'] = $this->mainObj->field('container_id');
                        
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Contactgroup']['uuid'] =  $this->checkUUID($line_parts[1]);
                        $saveDataArray['Container']['parent_id'] = "1";
                        $saveDataArray['Container']['name'] =  $this->checkUUID($line_parts[1]);
                        $saveDataArray['Container']['containertype_id'] = 6;
                    }
                    break;
                case "alias":
                    $saveDataArray['Contactgroup']['description'] = trim($line_parts[1]);
                    break;
                case "members":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contacts,$id);
                        }
                    }
                    $saveDataArray['Contactgroup']['Contact'] = $contacts;
                    $saveDataArray['Contact'] = $contacts;
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->contactgroups_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $saveDataArray = null;
                    $container = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readContactsConfigFile($cfg_file, $newObj = false, $override){
        $host_commands = null;
        $service_commands = null;
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define contact{":
                    $newObj = true;
                    $this->mainObj = new Contact();
                    $host_commands = array();
                    $service_commands = array();
                    $saveDataArray = array();
                    break;
                case "contact_name":
                    $erg = $this->findIDfromUUID($this->mainObj, "Contact", $line_parts[1]);
                    if ($erg != null) {
                        $saveDataArray['Contact']['id'] = $erg;
                        $this->mainObj->id = $erg;
                        $this->mainObj->uuid = null;
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Contact']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Contact']['name'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Contact']['phone'] = '';
                        $saveDataArray['Container']['Container'][0] = '1';
                    }
                    break;
                case "alias":
                    $saveDataArray['Contact']['description'] =  trim($line_parts[1]);
                    break;
                case "host_notifications_enabled":
                    $saveDataArray['Contact']['host_notifications_enabled'] =  trim($line_parts[1]);
                    break;
                case "service_notifications_enabled":
                    $saveDataArray['Contact']['service_notifications_enabled'] =  trim($line_parts[1]);
                    break;
                case "host_notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Contact']['host_timeperiod_id'] = $id;
                    }
                    break;
                case "service_notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Contact']['service_timeperiod_id'] = $id;
                    }
                    break;
                case "host_notification_commands":
                    $command_list = explode(",",$line_parts[1]);
                    foreach ($command_list as $tmpCommand) {
                        $command = new Command();
                        $id = $this->findIDfromUUID($command, "Command", $tmpCommand);
                        if ($id != null) {
                            array_push($host_commands, $id);
                        }
                    }
                    $saveDataArray['Contact']['HostCommands'] =  $host_commands;
                    break;
                case "service_notification_commands":
                    $command_list = explode(",",$line_parts[1]);
                    foreach ($command_list as $tmpCommand) {
                        $command = new Command();
                        $id = $this->findIDfromUUID($command, "Command", $tmpCommand);
                        if ($id != null) {
                            array_push($service_commands, $id);
                        }
                    }
                    $saveDataArray['Contact']['ServiceCommands'] =  $service_commands;
                    break;
                case "host_notification_options":
                    $host_options_notify = explode(",",trim($line_parts[1]));
                    foreach ($host_options_notify as $option){
                        switch ($option) {
                            case "t":
                                $saveDataArray['Contact']['notify_host_downtime'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Contact']['notify_host_unreachable'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Contact']['notify_host_recovery'] = 1;
                                break;
                            case "f":
                                $saveDataArray['Contact']['notify_host_flapping'] = 1;
                                break;
                            case "d":
                                $saveDataArray['Contact']['notify_host_down'] = 1;
                                break;
                        }
                    }
                    break;
                case "service_notification_options":
                    $service_options_notify = explode(",",trim($line_parts[1]));
                    foreach ($service_options_notify as $option){
                        switch ($option) {
                            case "d":
                                $saveDataArray['Contact']['notify_service_downtime'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Contact']['notify_service_unknown'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Contact']['notify_service_recovery'] = 1;
                                break;
                            case "f":
                                $saveDataArray['Contact']['notify_service_flapping'] = 1;
                                break;
                            case "c":
                                $saveDataArray['Contact']['notify_service_critical'] = 1;
                                break;
                            case "w":
                                $saveDataArray['Contact']['notify_service_warning'] = 1;
                                break;
                        }
                    }
                    break;
                case "email":
                    $saveDataArray['Contact']['email'] =  trim($line_parts[1]);
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->contacts_configutation['Contact'][$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $saveDataArray = null;
                    $container = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readHostdependencyConfigFile($cfg_file, $newObj = false, $override){
        //@TODO: Konfig anlegen und backup machen, um es parsen zu können
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostdependency{":
                    $newObj = true;
                    $this->mainObj = new Hostdependency();
                    $saveDataArray = array();
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->hostdependencies_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $saveDataArray = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readHostescalationConfigFile($cfg_file, $newObj = false, $override, $name){
        $contacts = null;
        $contact_groups = null;
        $hosts = null;
        $host_groups = null;
        $hostescalation = null;
        $saveDataArray = null;
        $hostescalationHostMembership = null;
        $hostescalationHostgroupMembership = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostescalation{":
                    $newObj = true;
                    $this->mainObj = new Hostescalation();
                    $contacts = array();
                    $contact_groups = array();
                    $hosts = array();
                    $host_groups = array();
                    $saveDataArray = array();
                    $hostescalation = array();
                    $hostescalationHostMembership = array();
                    $hostescalationHostgroupMembership = array();
                    $id = $this->findIDfromUUID($this->mainObj, "Hostescalation", $name);
                    if ($id != null) {
                        $hostescalation['id'] = $id;
                        $this->mainObj->uuid = null;
                        $this->mainObj->id = $id;
                        $hostescalation['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($name);
                        $this->mainObj->id = null;
                        $hostescalation['uuid'] = $this->checkUUID($name);
                    }
                    $hostescalation['Host_excluded'] = '';
                    $hostescalation['Hostgroup_excluded'] = '';
                    break;
                case "host_name":
                    $host = new Host();
                    $tmp_hosts = explode(",",$line_parts[1]);
                    foreach ($tmp_hosts as $tmp_host){
                        $tmpID = $this->findIDfromUUID($host, "Host", trim($tmp_host));
                        if ($tmpID != null) {
                            array_push($hosts, $tmpID);
                            $tmpArr = array();
                            $tmpArr['host_id'] = $tmpID;
                            $tmpArr['excluded'] = 0;
                            array_push($hostescalationHostMembership, $tmpArr);
                        }
                    }
                    $hostescalation['Host'] = $hosts;
                    break;
                case "hostgroup_name":
                    $host = new Hostgroup();
                    $tmp_hosts = explode(",",$line_parts[1]);
                    foreach ($tmp_hosts as $tmp_host){
                        $tmpID = $this->findIDfromUUID($host, "Hostgroup", trim($tmp_host));
                        if ($tmpID != null) {
                            array_push($host_groups, $tmpID);
                            $tmpArr = array();
                            $tmpArr['hostgroup_id'] = $tmpID;
                            $tmpArr['excluded'] = 0;
                            array_push($hostescalationHostgroupMembership, $tmpArr);
                        }
                    }
                    $hostescalation['Hostgroup'] = $host_groups;
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    $contact = new Contact();
                    foreach ($contact_list as $tmpContact) {
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contacts, $id);
                        }
                    }
                    $hostescalation['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    $contact = new Contactgroup();
                    foreach ($contact_list as $tmpContact) {
                        $id = $this->findIDfromUUID($contact, "Contactgroup", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $hostescalation['Contactgroup'] = $contact_groups;
                    break;
                case "first_notification":
                    $hostescalation['first_notification'] =  trim($line_parts[1]);
                    break;
                case "last_notification":
                    $hostescalation['last_notification'] =  trim($line_parts[1]);
                    break;
                case "notification_interval":
                    $hostescalation['notification_interval'] =  trim($line_parts[1]);
                    break;
                case "escalation_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $hostescalation['timeperiod_id'] = $id;
                    }
                    break;
                case "escalation_options":
                    $escalation_options = explode(",",trim($line_parts[1]));
                    foreach ($escalation_options as $option){
                        switch ($option) {
                            case "d":
                                $hostescalation['escalate_on_down'] = 1;
                                break;
                            case "u":
                                $hostescalation['escalate_on_unreachable'] = 1;
                                break;
                            case "r":
                                $hostescalation['escalate_on_recovery'] = 1;
                                break;
                        }
                    }
                    break;
                case "}":
                    $saveDataArray['Hostescalation'] = $hostescalation;
                    $saveDataArray['Contact'] = $contacts;
                    $saveDataArray['Contactgroups'] = $contact_groups;
                    $saveDataArray['HostescalationHostMembership'] = $hostescalationHostMembership;
                    $saveDataArray['HostescalationHostgroupMembership'] = $hostescalationHostgroupMembership;

                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->hostescalations_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }

                    $this->mainObj = null;
                    $contacts = null;
                    $contact_groups = null;
                    $hosts = null;
                    $host_groups = null;
                    $hostescalation = null;
                    $saveDataArray = null;
                    $hostescalationHostMembership = null;
                    $hostescalationHostgroupMembership = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readHostgroupConfigFile($cfg_file, $newObj = false, $override){
        $hosts = null;
        $hostgroup = null;
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostgroup{":
                    $newObj = true;
                    $this->mainObj = new Hostgroup();
                    $hostgroup = array();
                    $hosts = array();
                    $saveDataArray = array();
                    break;
                case "hostgroup_name":
                    $id = $this->findIDfromUUID($this->mainObj, "Hostgroup", $line_parts[1]);
                    if ($id != null){
                        $hostgroup['id'] = $id;
                        $this->mainObj->uuid = null;
                        $this->mainObj->id = $id;
                        $saveDataArray['Container']['id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $hostgroup['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Container']['name'] =  trim($line_parts[1]);
                        $saveDataArray['Container']['parent_id'] = 1;
                        $saveDataArray['Container']['containertype_id'] = 7;
                    }
                    break;
                case "alias":
                    $hostgroup['description'] = trim($line_parts[1]);
                    break;
                case "members":
                    $host_list = explode(",",$line_parts[1]);
                    foreach ($host_list as $tmpHost) {
                        $host = new Host();
                        $id = $this->findIDfromUUID($host, "Host", $tmpHost);
                        if ($id != null) {
                            array_push($hosts, $id);
                        }
                    }
                    $hostgroup['Host'] = $hosts;
                    break;
                case "}":
                    $saveDataArray['Hostgroup'] = $hostgroup;
                    $saveDataArray['Host'] = $hosts;
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->hostgroups_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $hosts = null;
                    $hostgroup = null;
                    $saveDataArray = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readHostConfigFile ($cfg_file, $newObj = false, $override) {
        $contacts = null;
        $contact_groups = null;
        $check_cmds = null;
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define host{":
                    $newObj = true;
                    $this->mainObj = new Host();
                    $contacts = array();
                    $contact_groups = array();
                    $saveDataArray = array();
                    break;
                case "use":
                    $template = new Hosttemplate();
                    $id = $this->findIDfromUUID($template, "Hosttemplate", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Host']['hosttemplate_id'] = $id;
                    }
                    break;
                case "host_name":
                    $id = $this->findIDfromUUID($this->mainObj, "Host", $line_parts[1]);
                    if($id != null) {
                        $saveDataArray['Host']['id'] = $id;
                        $this->mainObj->uuid = null;
                        $this->mainObj->id = $id;
                        $saveDataArray['Host']['container_id'] = $this->mainObj->field('container_id');
                        $saveDataArray['Container']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Host']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Host']['container_id'] = "1";
                        $saveDataArray['Container']['container_id'] = "1";
                    }
                    break;
                case "display_name":
                    $saveDataArray['Host']['name'] = trim($line_parts[1]);
                    break;
                case "address":
                    $saveDataArray['Host']['address'] = trim($line_parts[1]);
                    break;
                case "check_command":
                    $command = new Command();
                    //Check if more Params available than the UUID
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Hostcommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Hostcommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    $saveDataArray['Hostcommandargumentvalue'] = $check_cmds;
                    $id = $this->findIDfromUUID($command, "Command", $uuid);
                    if ($id != null) {
                        $saveDataArray['Host']['command_id'] = $id;
                    }
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Host']['check_period_id'] = $id;
                    }
                    break;
                case "check_interval":
                    $saveDataArray['Host']['check_interval'] = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $saveDataArray['Host']['retry_interval'] = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $saveDataArray['Host']['max_check_attempts'] = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $saveDataArray['Host']['active_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $saveDataArray['Host']['passive_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $saveDataArray['Host']['notifications_enabled'] = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contacts, $id);
                        }
                    }
                    $saveDataArray['Contact']['Contact'] = $contacts;
                    $saveDataArray['Host']['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contactgroup();
                        $id = $this->findIDfromUUID($contact, "Contactgroup", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $saveDataArray['Contactgroup']['Contactgroup'] = $contact_groups;
                    $saveDataArray['Host']['Contactgroup'] = $contact_groups;
                    break;
                case "notification_interval":
                    $saveDataArray['Host']['notification_interval'] = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Host']['notify_period_id'] = $id;
                    }
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "t":
                                $saveDataArray['Host']['notify_on_downtime'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Host']['notify_on_unreachable'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Host']['notify_on_recovery'] = 1;
                                break;
                            case "f":
                                $saveDataArray['Host']['notify_on_flapping'] = 1;
                                break;
                            case "d":
                                $saveDataArray['Host']['notify_on_down'] = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $saveDataArray['Host']['flap_detection_enabled'] = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $saveDataArray['Host']['process_performance_data'] = trim($line_parts[1]);
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->hosts_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $contact_groups = null;
                    $check_cmds = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readHosttemplateConfigFile($cfg_file, $newObj = false, $override){
        $contacts = null;
        $contact_groups = null;
        $check_cmds = null;
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define host{":
                    $newObj = true;
                    $this->mainObj = new Hosttemplate();
                    $saveDataArray = array();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    break;
                case "name":
                    $id =$this->findIDfromUUID($this->mainObj, "Hosttemplate", $line_parts[1]);
                    if($id != null) {
                        $saveDataArray['Hosttemplate']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Hosttemplate']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Hosttemplate']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Hosttemplate']['container_id'] = "1";
                        $saveDataArray['Hosttemplate']['priority'] = "1";
                        $saveDataArray['Hosttemplate']['Contactgroup'] = array();
                        $saveDataArray['Contactgroup'] = array();
                    }
                    break;
                    break;
                case "display_name":
                    $saveDataArray['Hosttemplate']['name'] = trim($line_parts[1]);
                    break;
                case "alias":
                    $saveDataArray['Hosttemplate']['description'] = trim($line_parts[1]);
                    break;
                case "check_command":
                    $command = new Command();
                    //Check if more Params available than the UUID
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Hosttemplatecommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Hosttemplatecommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    $saveDataArray['Hosttemplatecommandargumentvalue'] = $check_cmds;
                    $id = $this->findIDfromUUID($command, "Command", $uuid);
                    if ($id != null) {
                        $saveDataArray['Hosttemplate']['command_id'] = $id;
                    }
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Hosttemplate']['check_period_id'] = $id;
                    }
                    break;
                case "check_interval":
                    $saveDataArray['Hosttemplate']['check_interval'] = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $saveDataArray['Hosttemplate']['retry_interval'] = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $saveDataArray['Hosttemplate']['max_check_attempts'] = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $saveDataArray['Hosttemplate']['active_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $saveDataArray['Hosttemplate']['passive_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $saveDataArray['Hosttemplate']['notifications_enabled'] = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null){
                            array_push($contacts, $id);
                        }
                    }
                    $saveDataArray['Hosttemplate']['Contact'] = $contacts;
                    $saveDataArray['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contactgroup();
                        $id = $this->findIDfromUUID($contact, "Contactgroup", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $saveDataArray['Hosttemplate']['Contactgroup'] = $contacts;
                    $saveDataArray['Contactgroup'] = $contacts;
                    break;
                case "notification_interval":
                    $saveDataArray['Hosttemplate']['notification_interval'] = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Hosttemplate']['notify_period_id'] = $id;
                    }
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "t":
                                $saveDataArray['Hosttemplate']['notify_on_downtime'] = "1";
                                break;
                            case "u":
                                $saveDataArray['Hosttemplate']['notify_on_unreachable'] = "1";
                                break;
                            case "r":
                                $saveDataArray['Hosttemplate']['notify_on_recovery'] = "1";
                                break;
                            case "f":
                                $saveDataArray['Hosttemplate']['notify_on_flapping'] = "1";
                                break;
                            case "d":
                                $saveDataArray['Hosttemplate']['notify_on_down'] = "1";
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $saveDataArray['Hosttemplate']['flap_detection_enabled'] = trim($line_parts[1]);
                    break;
                case "flap_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "u":
                                $saveDataArray['Hosttemplate']['flap_detection_on_up'] = "1";
                                break;
                            case "d":
                                $saveDataArray['Hosttemplate']['flap_detection_on_down'] = "1";
                                break;
                            case "r":
                                $saveDataArray['Hosttemplate']['flap_detection_on_ubreachable'] = "1";
                                break;
                        }
                    }
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->hosttemplates_configutation['Hosttemplate'][$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $contact_groups = null;
                    $check_cmds = null;
                    $saveDataArray = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readServicedependencyConfigFile($cfg_file, $newObj = false, $override, $name){
        $service = null;
        $dependent_service = null;
        $saveDataArray = null;
        $servicedependencyServiceMembership = null;
        $servicedependencyServicegroupMembership = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define servicedependency{":
                    $newObj = true;
                    $this->mainObj = new Servicedependency();
                    $id = $this->findIDfromUUID($this->mainObj, "Servicedependency", $name);
                    if ($id != null){
                        $saveDataArray['Servicedependency']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Servicedependency']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($name);
                        $this->mainObj->id = null;
                        $saveDataArray['Servicedependency']['uuid'] = $this->checkUUID($name);
                        $saveDataArray['Servicedependency']['container_id'] = "1";
                    }
                    $servicedependencyServiceMembership = array();
                    $servicedependencyServicegroupMembership = array();
                    break;
                case "service_description":
                    $service = new Service();
                    $services = explode(",",$line_parts[1]);
                    $tmpArr = array();
                    foreach($services as $serv){
                        $id = $this->findIDfromUUID($service, "Service",$serv);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['service_id'] = $id;
                            $tmpServiceArray['dependent'] = 0;
                            array_push($servicedependencyServiceMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Servicedependency']['Service'] = $tmpArr;
                    break;
                case "dependent_service_description":
                    $dependent_service = new Service();
                    $dependent_services = explode(",",$line_parts[1]);
                    $tmpArr = array();
                    foreach($dependent_services as $dep_service) {
                        $id = $this->findIDfromUUID($service, "Service",$dep_service);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['service_id'] = $id;
                            $tmpServiceArray['dependent'] = 1;
                            array_push($servicedependencyServiceMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Servicedependency']['ServiceDependent'] = $tmpArr;
                    break;
                case "servicegroup_name":
                    $servicegroup = new Servicegroup();
                    $servicegroups = explode(",", $line_parts[1]);
                    $tmpArr = array();
                    foreach ($servicegroups as $group){
                        $id = $this->findIDfromUUID($servicegroup, "Servicegroup", $group);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['servicegroup_id'] = $id;
                            $tmpServiceArray['dependent'] = 0;
                            array_push($servicedependencyServicegroupMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Servicedependency']['Servicegroup'] = $tmpArr;
                    break;
                case "dependent_servicegroup_name":
                    $servicegroup = new Servicegroup();
                    $servicegroups = explode(",", $line_parts[1]);
                    $tmpArr = array();
                    foreach ($servicegroups as $group){
                        $id = $this->findIDfromUUID($servicegroup, "Servicegroup", $group);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['servicegroup_id'] = $id;
                            $tmpServiceArray['dependent'] = 1;
                            array_push($servicedependencyServicegroupMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Servicedependency']['ServicegroupDependent'] = $tmpArr;
                    break;
                case "inherits_parent":
                    $saveDataArray['Servicedependency']['inherits_parent'] = trim($line_parts[1]);
                    break;
                case "execution_failure_criteria":
                    $execution_failure = explode(",",trim($line_parts[1]));
                    foreach ($execution_failure as $option){
                        switch ($option) {
                            case "o":
                                $saveDataArray['Servicedependency']['execution_fail_on_ok'] = 1;
                                break;
                            case "w":
                                $saveDataArray['Servicedependency']['execution_fail_on_warning'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Servicedependency']['execution_fail_on_unknown'] = 1;
                                break;
                            case "c":
                                $saveDataArray['Servicedependency']['execution_fail_on_critical'] = 1;
                                break;
                            case "p":
                                $saveDataArray['Servicedependency']['execution_fail_on_pending'] = 1;
                                break;
                            case "n":
                                $saveDataArray['Servicedependency']['execution_none'] = 1;
                                break;
                        }
                    }
                    break;
                case "notification_failure_criteria":
                    $notification_failure = explode(",",trim($line_parts[1]));
                    foreach ($notification_failure as $option){
                        switch ($option) {
                            case "o":
                                $saveDataArray['Servicedependency']['notification_fail_on_ok'] = 1;
                                break;
                            case "w":
                                $saveDataArray['Servicedependency']['notification_fail_on_warning'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Servicedependency']['notification_fail_on_unknown'] = 1;
                                break;
                            case "c":
                                $saveDataArray['Servicedependency']['notification_fail_on_critical'] = 1;
                                break;
                            case "p":
                                $saveDataArray['Servicedependency']['notification_fail_on_pending'] = 1;
                                break;
                            case "n":
                                $saveDataArray['Servicedependency']['notification_none'] = 1;
                                break;
                        }
                    }
                    break;
                case "dependency_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Servicedependency']['timeperiod_id'] = $id;
                    }
                    break;
                case "}":
                    $saveDataArray['ServicedependencyServiceMembership'] = $servicedependencyServiceMembership;
                    $saveDataArray['ServicedependencyServicegroupMembership'] = $servicedependencyServicegroupMembership;
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->servicedependencies_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $service = null;
                    $dependent_service = null;
                    $saveDataArray = null;
                    $servicedependencyServiceMembership = null;
                    $servicedependencyServicegroupMembership = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readServiceescalationConfigFile($cfg_file, $newObj = false, $override, $name){
        $contacts = null;
        $contact_groups = null;
        $saveDataArray = null;
        $serviceescalationServiceMembership = null;
        $serviceescalationServicegroupMembership = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define serviceescalation{":
                    $newObj = true;
                    $this->mainObj = new Serviceescalation();
                    $contacts = array();
                    $contact_groups = array();
                    $saveDataArray = array();
                    $serviceescalationServiceMembership = array();
                    $serviceescalationServicegroupMembership = array();
                    $id = $this->findIDfromUUID($this->mainObj, "Serviceescalation", $name);
                    if($id != null) {
                        $saveDataArray['Serviceescalation']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Serviceescalation']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($name);
                        $this->mainObj->id = null;
                        $saveDataArray['Serviceescalation']['uuid'] = $this->checkUUID($name);
                        $saveDataArray['Serviceescalation']['container_id'] = 1;
                    }
                    break;
                case "service_description":
                    $service = new Service();
                    $servs = explode(",", $line_parts[1]);
                    $tmpArr = array();
                    foreach($servs as $group) {
                        $id = $this->findIDfromUUID($service, "Service", $group);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['service_id'] = $id;
                            $tmpServiceArray['dexcluded'] = 0;
                            array_push($serviceescalationServiceMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Serviceescalation']['Service'] = $tmpArr;
                    break;
                case "servicegroup_name":
                    $servicegroup = new Servicegroup();
                    $serv_groups = explode(",", $line_parts[1]);
                    $tmpArr = array();
                    foreach($serv_groups as $group) {
                        $id = $this->findIDfromUUID($servicegroup, "Servicegroup", $group);
                        if ($id != null) {
                            array_push($tmpArr, $id);
                            $tmpServiceArray = array();
                            $tmpServiceArray['servicegroup_id'] = $id;
                            $tmpServiceArray['dexcluded'] = 0;
                            array_push($serviceescalationServicegroupMembership, $tmpServiceArray);
                        }
                    }
                    $saveDataArray['Serviceescalation']['Servicegroup'] = $tmpArr;
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contacts, $id);
                         }
                    }
                    $saveDataArray['Serviceescalation']['Contact'] = $contacts;
                    $saveDataArray['Contact']['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $saveDataArray['Serviceescalation']['Contactgroup'] = $contact_groups;
                    $saveDataArray['Contactgroup']['Contactgroup'] = $contact_groups;
                    break;
                case "first_notification":
                    $saveDataArray['Serviceescalation']['first_notification'] =  trim($line_parts[1]);
                    break;
                case "last_notification":
                    $saveDataArray['Serviceescalation']['last_notification']=  trim($line_parts[1]);
                    break;
                case "notification_interval":
                    $saveDataArray['Serviceescalation']['notification_interval'] =  trim($line_parts[1]);
                    break;
                case "escalation_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Serviceescalation']['timeperiod_id'] = $id;
                    }
                    break;
                case "escalation_options":
                    $escalation_options = explode(",",trim($line_parts[1]));
                    foreach ($escalation_options as $option){
                        switch ($option) {
                            case "d":
                                $saveDataArray['Serviceescalation']['escalate_on_down'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Serviceescalation']['escalate_on_unreachable'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Serviceescalation']['escalato_on_recovery'] = 1;
                                break;
                        }
                    }
                    break;
                case "}":
                    $saveDataArray['ServiceescalationServiceMembership'] = $serviceescalationServiceMembership;
                    $saveDataArray['ServiceescalationServicegroupMembership'] = $serviceescalationServicegroupMembership;
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->servicescalations_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $contact_groups = null;
                    $saveDataArray = null;
                    $serviceescalationServiceMembership = null;
                    $serviceescalationServicegroupMembership = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readServicegroupConfigFile($cfg_file, $newObj = false, $override, $name) {
        $services = null;
        $saveDataArray = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define servicegroup{":
                    $newObj = true;
                    $this->mainObj = new Servicegroup();
                    $services = array();
                    $saveDataArray = array();
                    $id = $this->findIDfromUUID($this->mainObj, "Servicegroup", $name);
                    if ($id != null) {
                        $saveDataArray['Servicegroup']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Servicegroup']['container_id'] = $this->mainObj->field('container_id');
                        $saveDataArray['Container']['id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($name);
                        $this->mainObj->id = null;
                        $saveDataArray['uuid'] = $this->checkUUID($name);
                        $saveDataArray['Container']['name'] =  trim($name);
                        $saveDataArray['Container']['parent_id'] = 1;
                        $saveDataArray['Container']['containertype_id'] = 8;
                    }
                    break;
                case "alias":
                    $saveDataArray['Servicegroup']['description'] = trim($line_parts[1]);
                    break;
                case "members":
                    $service_list = explode(",",$line_parts[1]);
                    foreach ($service_list as $tmpService) {
                        $service = new Service();
                        $id = $this->findIDfromUUID($service, "Service", $tmpService);
                        if ($id != null){
                            array_push($services, $id);
                        }
                    }
                    $saveDataArray['Servicegroup']['Service'] = $services;
                    $saveDataArray['Service'] = $services;
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->servicegroups_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $services = null;
                    $saveDataArray = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readServiceConfigFile($cfg_file, $newObj = false, $override) {
        $contacts = null;
        $contact_groups = null;
        $saveDataArray = null;
        $service_groups = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define service{":
                    $newObj = true;
                    $this->mainObj = new Service();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    $service_groups = array();
                    $saveDataArray = array();
                    break;
                case "use":
                    $template = new Servicetemplate();
                    $id = $this->findIDfromUUID($template, "Servicetemplate", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Service']['servicetemplate_id'] = $id;
                    }
                    break;
                case "host_name":
                    $host = new Host();
                    $id =  $this->findIDfromUUID($host, "Host", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Service']['host_id'] = $id;
                    }
                    break;
                case "display_name":
                    $saveDataArray['Service']['name'] = trim($line_parts[1]);
                    break;
                case "name":
                    $id = $this->findIDfromUUID($this->mainObj, "Service", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Service']['id'] = $id;
                        $this->mainObj->uuid = null;
                        $this->mainObj->id = $id;
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Service']['uuid'] = $this->checkUUID($line_parts[1]);
                    }
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Service']['check_period_id'] = $id;
                    }
                    break;
                case "check_interval":
                    $saveDataArray['Service']['check_interval'] = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $saveDataArray['Service']['retry_interval'] = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $saveDataArray['Service']['max_check_attempts'] = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $saveDataArray['Service']['active_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $saveDataArray['Service']['passive_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $saveDataArray['Service']['notifications_enabled'] = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null){
                            array_push($contacts, $id);
                        }
                    }
                    $saveDataArray['Service']['Contact']['Contact'] = $contacts;
                    $saveDataArray['Contact']['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $saveDataArray['Service']['Contactgroup'] = $contacts;
                    $saveDataArray['Contactgroup']['Contactgroup'] = $contacts;
                    break;
                case "service_groups":
                    $service_group_list = explode(",",$line_parts[1]);
                    foreach ($service_group_list as $tmpServiceGroup) {
                        $servicegroup = new Servicegroup();
                        $id = $this->findIDfromUUID($servicegroup, "Servicegroup", $tmpServiceGroup);
                        if ($id != null) {
                            array_push($service_groups, $id);
                        }
                    }
                    $saveDataArray['Service']['Servicegroup'] = $service_groups;
                    $saveDataArray['Servicegroup']['Servicegroup'] = $service_groups;
                    break;
                case "notification_interval":
                    $saveDataArray['Service']['notification_interval'] = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Service']['notify_period_id'] = $id;
                    }
                    break;
                case "check_command":
                    $command = new Command();
                    //Prüfen, ob weitere Paramter außer der UUID vorhanden sind
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Servicecommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Servicecommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    //CommandID setzen
                    $id = $this->findIDfromUUID($command, "Command", $uuid);
                    if ($id != null) {
                        $saveDataArray['Service']['command_id'] = $id;
                    }
                    $saveDataArray['Servicecommandargumentvalue'] = $check_cmds;
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "w":
                                $saveDataArray['Service']['notify_on_warn'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Service']['notify_on_unknown'] = 1;
                                break;
                            case "c":
                                $saveDataArray['Service']['notify_on_critical'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Service']['notify_on_recovery'] = 1;
                                break;
                            case "f":
                                $saveDataArray['Service']['notify_on_flapping'] = 1;
                                break;
                            case "d":
                                $saveDataArray['Service']['notify_on_downtime'] = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $saveDataArray['Service']['flap_detection_enabled'] = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $saveDataArray['Service']['process_performance_data'] = trim($line_parts[1]);
                    break;
                case "is_volatile":
                    $saveDataArray['Service']['is_volatile'] = trim($line_parts[1]);
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->services_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $check_cmds = null;
                    $saveDataArray = null;
                    $contacts = null;
                    $contact_groups = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readServicetemplateConfigFile($cfg_file, $newObj = false, $override) {
        $contacts = null;
        $contact_groups = null;
        $saveDataArray = null;
        $check_cmds = null;
        $cnt = 0;
        for ($i = 0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define service{":
                    $newObj = true;
                    $this->mainObj = new Servicetemplate();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    $saveDataArray = array();
                    break;
                case "name":
                    $id = $this->findIDfromUUID($this->mainObj, "Servicetemplate", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Servicetemplate']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Servicetemplate']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Servicetemplate']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Servicetemplate']['container_id'] = "1";
                    }
                    break;
                case "display_name":
                    $saveDataArray['Servicetemplate']['name'] = trim($line_parts[1]);
                    break;
                case "service_description":
                    $saveDataArray['Servicetemplate']['description'] = trim($line_parts[1]);
                    break;
                case "check_command":
                    $command = new Command();
                    //Prüfen, ob weitere Paramter außer der UUID vorhanden sind
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Servicetemplatecommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Servicetemplatecommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    //CommandID setzen
                    $id = $this->findIDfromUUID($command, "Command", $uuid);
                    if ($id != null) {
                        $saveDataArray['Servicetemplate']['command_id'] = $id;
                    }
                    $saveDataArray['Servicetemplatecommandargumentvalue'] = $check_cmds;
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Servicetemplate']['check_period_id'] = $id;
                    }

                    break;
                case "check_interval":
                    $saveDataArray['Servicetemplate']['check_interval'] = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $saveDataArray['Servicetemplate']['retry_interval'] = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $saveDataArray['Servicetemplate']['max_check_attempts'] = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $saveDataArray['Servicetemplate']['active_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $saveDataArray['Servicetemplate']['passive_checks_enabled'] = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $saveDataArray['Servicetemplate']['notifications_enabled'] = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        array_push($contacts, $this->findIDfromUUID($contact, "Contact", $tmpContact));
                    }
                    $saveDataArray['Servicetemplate']['Contact'] = $contacts;
                    $saveDataArray['Contact']['Contact'] = $contacts;
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        if ($id != null) {
                            array_push($contact_groups, $id);
                        }
                    }
                    $saveDataArray['Servicetemplate']['Contactgroup'] = $contact_groups;
                    $saveDataArray['Contactgroup']['Contactgroup'] = $contact_groups;
                    break;
                case "notification_interval":
                    $saveDataArray['Servicetemplate']['notification_interval'] = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Servicetemplate']['notify_period_id'] = $id;
                    }
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "w":
                                $saveDataArray['Servicetemplate']['notify_on_warn'] = 1;
                                break;
                            case "u":
                                $saveDataArray['Servicetemplate']['notify_on_unknown'] = 1;
                                break;
                            case "c":
                                $saveDataArray['Servicetemplate']['notify_on_critical'] = 1;
                                break;
                            case "r":
                                $saveDataArray['Servicetemplate']['notify_on_recovery'] = 1;
                                break;
                            case "f":
                                $saveDataArray['Servicetemplate']['notify_on_flapping'] = 1;
                                break;
                            case "d":
                                $saveDataArray['Servicetemplate']['notify_on_downtime'] = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $saveDataArray['Servicetemplate']['flap_detection_enabled'] = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $saveDataArray['Servicetemplate']['process_performance_data'] = trim($line_parts[1]);
                    break;
                case "is_volatile":
                    $saveDataArray['Servicetemplate']['is_volatile'] = trim($line_parts[1]);
                    break;
                case "}":
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->servicetemplates_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        $this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    $contacts = null;
                    $contact_groups = null;
                    $saveDataArray = null;
                    $check_cmds = null;
                    break;
            }
        }
        return $newObj;
    }

    private function readTimeperiodConfigFile($cfg_file, $newObj = false, $override) {
        $saveDataArray = null;
        $timerangeArr = null;
        $id = null;
        $cnt = 0;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define timeperiod{":
                    $newObj = true;
                    $this->mainObj = new Timeperiod();
                    $saveDataArray = array();
                    $timerangeArr = array();
                    break;
                case "timeperiod_name":
                    $id =  $this->findIDfromUUID($this->mainObj, "Timeperiod", $line_parts[1]);
                    if ($id != null) {
                        $saveDataArray['Timeperiod']['id'] = $id;
                        $this->mainObj->id = $id;
                        $this->mainObj->uuid = null;
                        $saveDataArray['Timeperiod']['container_id'] = $this->mainObj->field('container_id');
                    } else {
                        $this->mainObj->uuid = $this->checkUUID($line_parts[1]);
                        $this->mainObj->id = null;
                        $saveDataArray['Timeperiod']['uuid'] = $this->checkUUID($line_parts[1]);
                        $saveDataArray['Timeperiod']['container_id'] = 1;
                    }
                    break;
                case "alias":
                    $saveDataArray['Timeperiod']['name'] = trim($line_parts[1]);
                    $saveDataArray['Timeperiod']['description'] = trim($line_parts[1]);
                    break;
                case "monday":
                    $this->addTimerange(1,$line_parts[1], $id, $timerangeArr);
                    break;
                case "tuesday":
                    $this->addTimerange(2,$line_parts[1], $id, $timerangeArr);
                    break;
                case "wednesday":
                    $this->addTimerange(3,$line_parts[1], $id, $timerangeArr);
                    break;
                case "thursday":
                    $this->addTimerange(4,$line_parts[1], $id, $timerangeArr);
                    break;
                case "friday":
                    $this->addTimerange(5,$line_parts[1], $id, $timerangeArr);
                    break;
                case "saturday":
                    $this->addTimerange(6,$line_parts[1], $id, $timerangeArr);
                    break;
                case "sunday":
                    $this->addTimerange(7,$line_parts[1], $id, $timerangeArr);
                    break;
                case "}":
                    $saveDataArray['Timerange'] = $timerangeArr;
                    $saveDataArray['template'] = array();
                    if (($override) || (!$override && $this->mainObj->uuid != null)) {
                        $this->timeperiods_configutation[$cnt] = $saveDataArray;
                        $cnt++;
                        //$this->mainObj->saveAll($saveDataArray);
                    }
                    $this->mainObj = null;
                    break;
            }
        }
        return $newObj;
    }

    private function addTimerange($day, $line, $timeperiod_id, &$array){
        $timespan_parts = explode("-",$line);
        $tmpArray = array();
        $tmpArray['timeperiod_id'] = $timeperiod_id;
        $tmpArray['day'] = $day;
        $tmpArray['start'] = $timespan_parts[0];
        $tmpArray['end'] = $timespan_parts[1];
        array_push($array, $tmpArray);
    }

    private function findIDfromUUID($objekt, $type, $uuid) {
        $erg = $objekt->findByUuid($uuid);
        //Check if the config was in database. If not make an new object
        if(sizeof($erg) > 0) {
            return $erg[$type]["id"];
        }else{
            return null;
        }
    }

    private function readCheckCommand($line, $objekt_cmd_args, $command, $type, &$array){
        //Check the configstring, if there are more parameter
        $params = explode(";", $line);
        $command_params = explode("!", $params[0]);
        $command_names = explode("!", trim($params[1]));
        $uuid = $command_params[0];
        $command_args = new Commandargument();
        $erg = $command_args->find('all', array('conditions' => array('Commandargument.command_id =' => $this->findIDfromUUID($command, "Command", $uuid))));
        foreach ($erg as $commandargument) {
            //Spezifisches Commandargument anhand der Commandargument ID's auslesen
            $service_args = $objekt_cmd_args->find('all', array('conditions' => array($type.'.commandargument_id =' => $commandargument["Commandargument"]["id"])));
            $cnt = 0;
            $tmpArr = array();
            foreach ($command_names as $name) {
                //Werte für spezifisches commandargument setzen
                if ($commandargument["Commandargument"]["human_name"] == $name) {
                    $tmpArr['value'] = $command_params[$cnt + 1];
                    $tmpArr['commandargument_id'] = $commandargument["Commandargument"]["id"];
                    $array[$commandargument["Commandargument"]["id"]] = $tmpArr;
                    break;
                }
                $cnt++;
            }
        }
        return $uuid;
    }

    private function makeDifferenz($first, $second){

        if($first > $second) {
            $td['dif'][0] = $first - $second;
        }
        else {
            $td['dif'][0] = $second - $first;
        }

        $td['sec'][0] = $td['dif'][0] % 60; // 67 = 7

        $td['min'][0] = (($td['dif'][0] - $td['sec'][0]) / 60) % 60;

        $td['std'][0] = (((($td['dif'][0] - $td['sec'][0]) /60)-
                    $td['min'][0]) / 60) % 24;

        $td['day'][0] = floor( ((((($td['dif'][0] - $td['sec'][0]) /60)-
                    $td['min'][0]) / 60) / 24) );

        $td = $this->makeString($td);

        return $td;
    }

    private function makeString($td){
        $string = "";
        if ($td['sec'][0] == 1) {
            $string = $string." ".$td['sec'][0]." ".$td['sec'][1] = 'Sekunde';
        }
        else {
            $string = $string." ".$td['sec'][0]." ".$td['sec'][1] = 'Sekunden';
        }

        if ($td['min'][0] == 1) {
            $string = $string." ".$td['min'][0]." ".$td['min'][1] = 'Minute';
        }
        else {
            $string = $string." ".$td['min'][0]." ".$td['min'][1] = 'Minuten';
        }

        if ($td['std'][0] == 1) {
            $string = $string." ".$td['std'][0]." ".$td['std'][1] = 'Stunde';
        }
        else {
            $string = $string." ".$td['std'][0]." ".$td['std'][1] = 'Stunden';
        }

        if ($td['day'][0] == 1) {
            $string = $string." ".$td['day'][0]." ".$td['day'][1] = 'Tag';
        }
        else {
            $string = $string." ".$td['day'][0]." ".$td['day'][1] = 'Tage';
        }

        return $string;
    }
    
    private function checkUUID($uuid) {
        if (Validation::uuid($uuid)) {
            return $uuid;
        } else {
            App::uses('UUID', 'Lib');
            $uuid = UUID::v4();
            return $uuid;
        }
    }
}
