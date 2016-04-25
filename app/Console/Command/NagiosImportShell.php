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
class NagiosImportShell extends AppShell {
    /* @var object Object Instance for access to specific object functions */
    private $mainObj = null;

    public function main(){
        $this->out('### Welcome to the nagios configuration import ###');
        $start = time();
        $backup_dir = $this->chooseBackupDir();
        $this->parseDir($backup_dir);
        $ende = time();
        $laufzeit = $ende - $start;
        $this->out("Duration of Import: ".$laufzeit);
        $this->out('### End of importing Configuration ###');
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
        return $backup_dirs[$import_dir];
    }

    public function parseDir($path) {
        $all_files = scandir($path);
        foreach ($all_files as $file) {
            if (is_dir($path."/".$file) && $file != "." && $file != "..") {
                $config_files = scandir($path."/".$file);
                $this->out("<info>Configurations for ".$file." are in progress.</info>");
                if (sizeof($config_files) == 2) {
                    $this->out("<warning>No Configfile for ".$file." defined</warning>");
                }
                foreach ($config_files as $config_file) {
                    if (!is_dir($config_file)) {
                        $this->parseFile($path."/".$file."/".$config_file);
                    }
                }
            }
        }
    }
    /*
    * Parse the given File
    */
    public function parseFile($file) {
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
                    if (!$this->readCommandConfigFile($cfg_file)) {
                        $this->out("<warning>No Command defined</warning>");
                    }
                    break;
                case "contactgroups":
                    //Read the config file and check if it was empty
                    if (!$this->readContactgroupConfigFile($cfg_file)) {
                        $this->out("<warning>No Contactgroup defined</warning>");
                    }
                    break;
                case "contacts":
                    //Read the config file and check if it was empty
                    if (!$this->readContactsConfigFile($cfg_file)) {
                        $this->out("<warning>No Contact defined</warning>");
                    }
                    break;
                case "hostdependencies":
                    //Read the config file and check if it was empty
                    if (!$this->readHostdependencyConfigFile($cfg_file)) {
                        $this->out("<warning>No Hostdependencie defined</warning>");
                    }
                    break;
                case "hostescalations":
                    //Read the config file and check if it was empty
                    if (!$this->readHostescalationConfigFile($cfg_file, false, null, $config_name[0])) {
                        $this->out("<warning>No Hostescalation defined</warning>");
                    }
                    break;
                case "hostgroups":
                    if (!$this->readHostgroupConfigFile($cfg_file)) {
                        $this->out("<warning>No Hostgroup defined</warning>");
                    }
                    break;
                case "hosts":
                    //Read the config file and check if it was empty
                    if (!$this->readHostConfigFile($cfg_file)) {
                        $this->out("<warning>No Host defined</warning>");
                    }
                    break;
                case "hosttemplates":
                    //Read the config file and check if it was empty
                    if (!$this->readHosttemplateConfigFile($cfg_file)) {
                        $this->out("<warning>No Hosttemplate defined</warning>");
                    }
                    break;
                case "servicedependencies":
                    if (!$this->readServicedependencyConfigFile($cfg_file, false, null, $config_name[0])) {
                        $this->out("<warning>No Service-Dependencies defined</warning>");
                    }
                    break;
                case "serviceescalations":
                    //Read the config file and check if it was empty
                    if (!$this->readServiceescalationConfigFile($cfg_file, false, null, $config_name[0])) {
                        $this->out("<warning>No Service-Escalation defined</warning>");
                    }
                    break;
                case "servicegroups":
                    //Read the config file and check if it was empty
                    if (!$this->readServicegroupConfigFile($cfg_file, false, null, $config_name[0])) {
                        $this->out("<warning>No Service-Group defined</warning>");
                    }
                    break;
                case "services":
                    //Read the config file and check if it was empty
                    if (!$this->readServiceConfigFile($cfg_file)) {
                        $this->out("<warning>No Service defined</warning>");
                    }
                    break;
                case "servicetemplates":
                    //Read the config file and check if it was empty
                    if (!$this->readServicetemplateConfigFile($cfg_file)) {
                        $this->out("<warning>No Service-Template defined</warning>");
                    }
                    break;
                case "timeperiods":
                    //Read the config file and check if it was empty
                    if (!$this->readTimeperiodConfigFile($cfg_file)) {
                        $this->out("<warning>No Service-Group defined</warning>");
                    }
                    break;
                case "defaults":
                    //@TODO: Müssen die Default Configs auch in die Datenbank (scheinen nicht abgelegt zu sein)?
                    $this->out("Defaults");
                    break;
            }
        } else {
            $this->out("<error>!!! ".$file." is not a valid Config File or suffix is wrong !!!</error>");
        }
    }

    private function readCommandConfigFile($cfg_file, $newObj = false, $tmpObj = null){
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define command{":
                    $this->out("--------------------------------------");
                    $newObj = true;
                    $this->mainObj = new Command();
                    $tmpObj = new Command();
                    break;
                case "command_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Command", $line_parts[1]);
                    break;
                case "command_line":
                    $tmpObj->command_line = trim($line_parts[1]);
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    //Objekt speichern
                    $this->mainObj->save($tmpObj);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readContactgroupConfigFile($cfg_file, $newObj = false, $tmpObj = null) {
        $contacts = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define contactgroup{":
                    $newObj = true;
                    $this->mainObj = new Contactgroup();
                    $tmpObj = new Contactgroup();
                    $contacts = array();
                    $this->out("--------------------------------------");
                    break;
                case "contactgroup_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Contactgroup", $line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->description = trim($line_parts[1]);
                    break;
                case "members":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    //Objekt und zugehörige Contacts speichern
                    $this->mainObj->save($tmpObj);
                    $this->mainObj->save($contacts);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readContactsConfigFile($cfg_file, $newObj = false, $tmpObj = null){
        $host_commands = null;
        $service_commands = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define contact{":
                    $newObj = true;
                    $this->mainObj = new Contact();
                    $tmpObj = new Contact();
                    $host_commands = array();
                    $service_commands = array();
                    $this->out("--------------------------------------");
                    break;
                case "contact_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Contact", $line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->description =  trim($line_parts[1]);
                    break;
                case "host_notifications_enabled":
                    $tmpObj->host_notifications_enabled =  trim($line_parts[1]);
                    break;
                case "service_notifications_enabled":
                    $tmpObj->service_notifications_enabled =  trim($line_parts[1]);
                    break;
                case "host_notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->host_timeperiod_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "service_notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->service_timeperiod_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "host_notification_commands":
                    $command_list = explode(",",$line_parts[1]);
                    foreach ($command_list as $tmpCommand) {
                        $command = new Command();
                        $command->id = $this->findIDfromUUID($command, "Command", $tmpCommand);
                        array_push($host_commands, $command);
                    }
                    break;
                case "service_notification_commands":
                    $command_list = explode(",",$line_parts[1]);
                    foreach ($command_list as $tmpCommand) {
                        $command = new Command();
                        $command->id = $this->findIDfromUUID($command, "Command", $tmpCommand);
                        array_push($service_commands, $command);
                    }
                    break;
                case "host_notification_options":
                    $host_options_notify = explode(",",trim($line_parts[1]));
                    foreach ($host_options_notify as $option){
                        switch ($option) {
                            case "t":
                                $tmpObj->notify_host_downtime = 1;
                                break;
                            case "u":
                                $tmpObj->notify_host_unreachable = 1;
                                break;
                            case "r":
                                $tmpObj->notify_host_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_host_flapping = 1;
                                break;
                            case "d":
                                $tmpObj->notify_host_down = 1;
                                break;
                        }
                    }
                    break;
                case "service_notification_options":
                    $service_options_notify = explode(",",trim($line_parts[1]));
                    foreach ($service_options_notify as $option){
                        switch ($option) {
                            case "d":
                                $tmpObj->notify_service_downtime = 1;
                                break;
                            case "u":
                                $tmpObj->notify_service_unknown = 1;
                                break;
                            case "r":
                                $tmpObj->notify_service_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_service_flapping = 1;
                                break;
                            case "c":
                                $tmpObj->notify_service_critical = 1;
                                break;
                            case "w":
                                $tmpObj->notify_service_warning = 1;
                                break;
                        }
                    }
                    break;
                case "email":
                    $tmpObj->email =  trim($line_parts[1]);
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readHostdependencyConfigFile($cfg_file, $newObj = false, $tmpObj = null){
        //@TODO: Konfig anlegen und backup machen, um es parsen zu können
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostdependencie{":
                    $newObj = true;
                    $this->mainObj = new Hostdependency();
                    $tmpObj = new Hostdependency();
                    $this->out("--------------------------------------");
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readHostescalationConfigFile($cfg_file, $newObj = false, $tmpObj = null, $name){
        $contacts = null;
        $contact_groups = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostescalation{":
                    $newObj = true;
                    $this->mainObj = new Hostescalation();
                    $tmpObj = new Hostescalation();
                    $contacts = array();
                    $contact_groups = array();
                    $tmpObj->uuid = $name;
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Hostescalation", $name);
                    $this->out("--------------------------------------");
                    break;
                case "host_name":
                    $host = new Host();
                    $host->id = $this->findIDfromUUID($host, "Host", $line_parts[1]);
                    $tmpObj->Host = $host;
                    break;
                case "hostgroup_name":
                    $host = new Hostgroup();
                    $host->id = $this->findIDfromUUID($host, "Hostgroup", $line_parts[1]);
                    $tmpObj->Hostgroup = $host;
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "first_notification":
                    $tmpObj->first_notification =  trim($line_parts[1]);
                    break;
                case "last_notification":
                    $tmpObj->last_notification =  trim($line_parts[1]);
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval =  trim($line_parts[1]);
                    break;
                case "escalation_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->timeperiod_id  = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "escalation_options":
                    $escalation_options = explode(",",trim($line_parts[1]));
                    foreach ($escalation_options as $option){
                        switch ($option) {
                            case "d":
                                $tmpObj->escalate_on_down = 1;
                                break;
                            case "u":
                                $tmpObj->escalate_on_unreachable = 1;
                                break;
                            case "r":
                                $tmpObj->escalato_on_recovery = 1;
                                break;
                        }
                    }
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readHostgroupConfigFile($cfg_file, $newObj = false, $tmpObj = null){
        $hosts = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define hostgroup{":
                    $newObj = true;
                    $this->mainObj = new Hostgroup();
                    $tmpObj = new Hostgroup();
                    $hosts = array();
                    $this->out("--------------------------------------");
                    break;
                case "hostgroup_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Hostgroup", $line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->description = trim($line_parts[1]);
                    break;
                case "members":
                    $host_list = explode(",",$line_parts[1]);
                    foreach ($host_list as $tmpHost) {
                        $host = new Host();
                        $host->id = $this->findIDfromUUID($host, "Host", $tmpHost);
                        array_push($hosts, $host);
                    }
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readHostConfigFile ($cfg_file, $newObj = false, $tmpObj = null) {
        $contacts = null;
        $contact_groups = null;
        $check_cmds = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define host{":
                    $newObj = true;
                    $this->mainObj = new Host();
                    $tmpObj = new Host();
                    $contacts = array();
                    $contact_groups = array();
                    $this->out("--------------------------------------");
                    break;
                case "use":
                    $template = new Hosttemplate();
                    $tmpObj->use  = $this->findIDfromUUID($template, "Hosttemplate", $line_parts[1]);
                    break;
                case "host_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Host", $line_parts[1]);
                    break;
                case "display_name":
                    $tmpObj->name = trim($line_parts[1]);
                    break;
                case "address":
                    $tmpObj->address = trim($line_parts[1]);
                    break;
                case "check_command":
                    $command = new Command();
                    //Prüfen, ob weitere Paramter außer der UUID vorhanden sind
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Hostcommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Hostcommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    //CommandID setzen
                    $tmpObj->command_id = $this->findIDfromUUID($command, "Command", $uuid);
                    break;
                case "initial_state":
                    //@TODO: Wo wird das gespeichert?
                    //Wert: u
                    //$cmd_line = explode("initial_state", $line);
                    //$this->out("Init-State: " . trim($cmd_line[1]));
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->check_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "check_interval":
                    $tmpObj->check_interval = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $tmpObj->retry_interval = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $tmpObj->max_check_attempts = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $tmpObj->active_checks_enabled = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $tmpObj->passive_checks_enabled = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $tmpObj->notifications_enabled = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->notify_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "t":
                                $tmpObj->notify_on_downtime = 1;
                                break;
                            case "u":
                                $tmpObj->notify_on_unreachable = 1;
                                break;
                            case "r":
                                $tmpObj->notify_on_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_on_flapping = 1;
                                break;
                            case "d":
                                $tmpObj->notify_on_down = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $tmpObj->flap_detection_enabled = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $tmpObj->process_performance_data = trim($line_parts[1]);
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readHosttemplateConfigFile($cfg_file, $newObj = false, $tmpObj = null){
        $contacts = null;
        $contact_groups = null;
        $check_cmds = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define host{":
                    $newObj = true;
                    $this->mainObj = new Hosttemplate();
                    $tmpObj = new Hosttemplate();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    $this->out("--------------------------------------");
                    break;
                case "register":
                    //@TODO: Was ist das?
                    //$cmd_name = explode("register", $line);
                    //$this->out("Register: " . trim($cmd_name[1]));
                    break;
                case "use":
                    //@TODO: Was ist das bzw wo wird das gespeichert?
                    //$template = new Hosttemplate();
                    //$template->id = $this->findIDfromUUID($template, "Hosttemplate", $line_parts[1]);
                    //$tmpObj->Hosttemplate = $template;
                    break;
                case "host_name":
                    //@TODO: Wird das benötigt? Selbe UUID wie bei name.
                    //$cmd_line = explode("host_name", $line);
                    //$tmpObj->name = trim($cmd_line[1]);
                    break;
                case "name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Hosttemplate", $line_parts[1]);
                    break;
                case "display_name":
                    $tmpObj->name = trim($line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->description = trim($line_parts[1]);
                    break;
                case "check_command":
                    $command = new Command();
                    //Prüfen, ob weitere Paramter außer der UUID vorhanden sind
                    if (strstr($line_parts[1], ";")) {
                        $service_cmd_args = new Hosttemplatecommandargumentvalue();
                        $uuid = $this->readCheckCommand($line_parts[1],$service_cmd_args,$command,"Hosttemplatecommandargumentvalue",$check_cmds);
                    } else {
                        $uuid = $line_parts[1];
                    }
                    //CommandID setzen
                    $tmpObj->command_id = $this->findIDfromUUID($command, "Command", $uuid);
                    break;
                case "initial_state":
                    //@TODO: Wo wird das gespeichert?
                    //Wert: u
                    //$cmd_line = explode("initial_state", $line);
                    //$this->out("Init-State: " . trim($cmd_line[1]));
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->check_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "check_interval":
                    $tmpObj->check_interval = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $tmpObj->retry_interval = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $tmpObj->max_check_attempts = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $tmpObj->active_checks_enabled = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $tmpObj->passive_checks_enabled = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $tmpObj->notifications_enabled = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->notify_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "t":
                                $tmpObj->notify_on_downtime = 1;
                                break;
                            case "u":
                                $tmpObj->notify_on_unreachable = 1;
                                break;
                            case "r":
                                $tmpObj->notify_on_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_on_flapping = 1;
                                break;
                            case "d":
                                $tmpObj->notify_on_down = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $tmpObj->flap_detection_enabled = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $tmpObj->process_performance_data = trim($line_parts[1]);
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $check_cmds = null;
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readServicedependencyConfigFile($cfg_file, $newObj = false, $tmpObj = null, $name){
        $host = null;
        $dependent_host = null;
        $service = null;
        $dependent_service = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define servicedependency{":
                    $newObj = true;
                    $this->mainObj = new Servicedependency();
                    $tmpObj = new Servicedependency();
                    $tmpObj->uuid = $name;
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Servicedependency", $name);
                    $this->out("--------------------------------------");
                    break;
                case "host_name":
                    $host = new Host();
                    $host->id = $this->findIDfromUUID($host, "Host", $line_parts[1]);
                    //$tmpObj->Host = $host;
                    break;
                case "service_description":
                    $service = new Service();
                    $service->id = $this->findIDfromUUID($service, "Service", $line_parts[1]);
                    //$tmpObj->Service = $service;
                    break;
                case "dependent_host_name":
                    $dependent_host = new Host();
                    $dependent_host->id = $this->findIDfromUUID($dependent_host, "Host", $line_parts[1]);
                    //$tmpObj->Host = $dependent_host;
                    break;
                case "dependent_service_description":
                    $dependent_service = new Service();
                    $dependent_service->id = $this->findIDfromUUID($dependent_service, "Service", $line_parts[1]);
                    //$tmpObj->Service = $service;
                    break;
                case "servicegroup_name":
                    $servicegroup = new Servicegroup();
                    $servicegroup->id = $this->findIDfromUUID($servicegroup, "Servicegroup", $line_parts[1]);
                    $tmpObj->Servicegroup = $servicegroup;
                    break;
                case "inherits_parent":
                    $tmpObj->inherits_parent = trim($line_parts[1]);
                    break;
                case "execution_failure_criteria":
                    $execution_failure = explode(",",trim($line_parts[1]));
                    foreach ($execution_failure as $option){
                        switch ($option) {
                            case "o":
                                $tmpObj->execution_fail_on_ok = 1;
                                break;
                            case "w":
                                $tmpObj->execution_fail_on_warning = 1;
                                break;
                            case "u":
                                $tmpObj->execution_fail_on_unknown = 1;
                                break;
                            case "c":
                                $tmpObj->execution_fail_on_critical = 1;
                                break;
                            case "p":
                                $tmpObj->execution_fail_on_pending = 1;
                                break;
                            case "n":
                                $tmpObj->execution_none = 1;
                                break;
                        }
                    }
                    break;
                case "notification_failure_criteria":
                    $notification_failure = explode(",",trim($line_parts[1]));
                    foreach ($notification_failure as $option){
                        switch ($option) {
                            case "o":
                                $tmpObj->notification_fail_on_ok = 1;
                                break;
                            case "w":
                                $tmpObj->notification_fail_on_warning = 1;
                                break;
                            case "u":
                                $tmpObj->notification_fail_on_unknown = 1;
                                break;
                            case "c":
                                $tmpObj->notification_fail_on_critical = 1;
                                break;
                            case "p":
                                $tmpObj->notification_fail_on_pending = 1;
                                break;
                            case "n":
                                $tmpObj->notification_none = 1;
                                break;
                        }
                    }
                    break;
                case "dependency_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->timeperiod_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "}":
                    //@TODO: Erzeugte Objekte noch hinzufügen und speichern.
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readServiceescalationConfigFile($cfg_file, $newObj = false, $tmpObj = null, $name){
        $contacts = null;
        $contact_groups = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define serviceescalation{":
                    $newObj = true;
                    $this->mainObj = new Serviceescalation();
                    $tmpObj = new Serviceescalation();
                    $tmpObj->uuid = $name;
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Serviceescalation", $name);
                    $contacts = array();
                    $contact_groups = array();
                    $this->out("--------------------------------------");
                    break;
                case "host_name":
                    $host = new Host();
                    $host->id = $this->findIDfromUUID($host, "Host", $line_parts[1]);
                    //$tmpObj->Host = $host;
                    break;
                case "service_description":
                    $service = new Service();
                    $service->id = $this->findIDfromUUID($service, "Service", $line_parts[1]);
                    //$tmpObj->Service = $service;
                    break;
                case "servicegroup_name":
                    $servicegroup = new Servicegroup();
                    $servicegroup->id = $this->findIDfromUUID($servicegroup, "Servicegroup", $line_parts[1]);
                    //$tmpObj->Servicegroup = $servicegroup;
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "first_notification":
                    $tmpObj->first_notification =  trim($line_parts[1]);
                    break;
                case "last_notification":
                    $tmpObj->last_notification =  trim($line_parts[1]);
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval =  trim($line_parts[1]);
                    break;
                case "escalation_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->timeperiod_id  = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "escalation_options":
                    $escalation_options = explode(",",trim($line_parts[1]));
                    foreach ($escalation_options as $option){
                        switch ($option) {
                            case "d":
                                $tmpObj->escalate_on_down = 1;
                                break;
                            case "u":
                                $tmpObj->escalate_on_unreachable = 1;
                                break;
                            case "r":
                                $tmpObj->escalato_on_recovery = 1;
                                break;
                        }
                    }
                    break;
                case "}":
                    //@TODO: Erzeugte Objekte noch hinzufügen und speichern.
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readServicegroupConfigFile($cfg_file, $newObj = false, $tmpObj = null, $name) {
        $services = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/', '#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]) {
                case "define servicegroup{":
                    $newObj = true;
                    $this->mainObj = new Servicegroup();
                    $tmpObj = new Servicegroup();
                    $tmpObj->uuid = $name;
                    $services = array();
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Servicegroup", $name);
                    $this->out("--------------------------------------");
                    break;
                case "servicegroup_name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Servicegroup", $line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->description = trim($line_parts[1]);
                    break;
                case "members":
                    $service_list = explode(",",$line_parts[1]);
                    foreach ($service_list as $tmpService) {
                        $service = new Service();
                        $service->id = $this->findIDfromUUID($service, "Service", $tmpService);
                        array_push($services, $service);
                    }
                    break;
                case "}":
                    //@TODO: Erzeugte Objekte noch hinzufügen und speichern.
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readServiceConfigFile($cfg_file, $newObj = false, $tmpObj = null) {
        $contacts = null;
        $contact_groups = null;
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define service{":
                    $this->out("--------------------------------------");
                    $newObj = true;
                    $this->mainObj = new Service();
                    $tmpObj = new Service();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    break;
                case "use":
                    $template = new Servicetemplate();
                    $tmpObj->servicetemplate_id = $this->findIDfromUUID($template, "Servicetemplate", $line_parts[1]);
                    break;
                case "host_name":
                    $host = new Host();
                    $tmpObj->host_id = $this->findIDfromUUID($host, "Host", $line_parts[1]);
                    break;
                case "display_name":
                    $tmpObj->name = trim($line_parts[1]);
                    break;
                case "name":
                    $tmpObj->uuid = trim(trim($line_parts[1]));
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Service", $line_parts[1]);
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->check_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "check_interval":
                    $tmpObj->check_interval = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $tmpObj->retry_interval = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $tmpObj->max_check_attempts = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $tmpObj->active_checks_enabled = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $tmpObj->passive_checks_enabled = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $tmpObj->notifications_enabled = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->notify_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
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
                    $tmpObj->command_id = $this->findIDfromUUID($command, "Command", $uuid);
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "w":
                                $tmpObj->notify_on_warn = 1;
                                break;
                            case "u":
                                $tmpObj->notify_on_unknown = 1;
                                break;
                            case "c":
                                $tmpObj->notify_on_critical = 1;
                                break;
                            case "r":
                                $tmpObj->notify_on_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_on_flapping = 1;
                                break;
                            case "d":
                                $tmpObj->notify_on_downtime = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $tmpObj->flap_detection_enabled = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $tmpObj->process_performance_data = trim($line_parts[1]);
                    break;
                case "is_volatile":
                    $tmpObj->is_volatile = trim($line_parts[1]);
                    break;
                case "}":
                    //@TODO: Erzeugte Objekte hinzufügen und speichern.
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $check_cmds = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readServicetemplateConfigFile($cfg_file, $newObj = false, $tmpObj = null) {
        $contacts = null;
        $contact_groups = null;
        for ($i = 0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define service{":
                    $newObj = true;
                    $this->mainObj = new Servicetemplate();
                    $tmpObj = new Servicetemplate();
                    $check_cmds = array();
                    $contacts = array();
                    $contact_groups = array();
                    $this->out("--------------------------------------");
                    break;
                case "register":
                    //@TODO: Was ist das? Wo soll das gespeichert werden?
                    //$cmd_name = explode("register", $line);
                    //$this->out("Register: " . trim($cmd_name[1]));
                    break;
                case "use":
                    //@TODO: Was ist das? Wo soll das gespeichert werden? Verknüpfungstabelle Servicetemplates to Servicetemplategroups
                    //$cmd_line = explode("use", $line);
                    //$tmpObj->servicetemplatetype_id = trim($cmd_line[1]);
                    break;
                case "name":
                    $tmpObj->uuid = trim($line_parts[1]);
                    $tmpObj->id = $this->findIDfromUUID($this->mainObj, "Servicetemplate", $line_parts[1]);
                    break;
                case "display_name":
                    $tmpObj->name = trim($line_parts[1]);
                    break;
                case "service_description":
                    $tmpObj->description = trim($line_parts[1]);
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
                    $tmpObj->command_id = $this->findIDfromUUID($command, "Command", $uuid);
                    break;
                case "initial_state":
                    //@TODO: Wo wird das gespeichert?
                    //Wert: u
                    //$cmd_line = explode("initial_state", $line);
                    //$this->out("Init-State: " . trim($cmd_line[1]));
                    break;
                case "check_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->check_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "check_interval":
                    $tmpObj->check_interval = trim($line_parts[1]);
                    break;
                case "retry_interval":
                    $tmpObj->retry_interval = trim($line_parts[1]);
                    break;
                case "max_check_attempts":
                    $tmpObj->max_check_attempts = trim($line_parts[1]);
                    break;
                case "active_checks_enabled":
                    $tmpObj->active_checks_enabled = trim($line_parts[1]);
                    break;
                case "passive_checks_enabled":
                    $tmpObj->passive_checks_enabled = trim($line_parts[1]);
                    break;
                case "notifications_enabled":
                    $tmpObj->notifications_enabled = trim($line_parts[1]);
                    break;
                case "contacts":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contacts,$contact);
                    }
                    break;
                case "contact_groups":
                    $contact_list = explode(",",$line_parts[1]);
                    foreach ($contact_list as $tmpContact) {
                        $contact = new Contact();
                        $contact->id = $this->findIDfromUUID($contact, "Contact", $tmpContact);
                        array_push($contact_groups,$contact);
                    }
                    break;
                case "notification_interval":
                    $tmpObj->notification_interval = trim($line_parts[1]);
                    break;
                case "notification_period":
                    $timeperiod = new Timeperiod();
                    $tmpObj->notify_period_id = $this->findIDfromUUID($timeperiod, "Timeperiod", $line_parts[1]);
                    break;
                case "notification_options":
                    $options_notify = explode(",",trim($line_parts[1]));
                    foreach ($options_notify as $option){
                        switch ($option) {
                            case "w":
                                $tmpObj->notify_on_warn = 1;
                                break;
                            case "u":
                                $tmpObj->notify_on_unknown = 1;
                                break;
                            case "c":
                                $tmpObj->notify_on_critical = 1;
                                break;
                            case "r":
                                $tmpObj->notify_on_recovery = 1;
                                break;
                            case "f":
                                $tmpObj->notify_on_flapping = 1;
                                break;
                            case "d":
                                $tmpObj->notify_on_downtime = 1;
                                break;
                        }
                    }
                    break;
                case "flap_detection_enabled":
                    $tmpObj->flap_detection_enabled = trim($line_parts[1]);
                    break;
                case "process_perf_data":
                    $tmpObj->process_performance_data = trim($line_parts[1]);
                    break;
                case "is_volatile":
                    $tmpObj->is_volatile = trim($line_parts[1]);
                    break;
                case "}":
                    //@TODO: Erzeugte Objekte hinzufügen und speichern
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj->Contact = $contacts;
                    $check_cmds = null;
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function readTimeperiodConfigFile($cfg_file, $newObj = false, $tmpObj = null) {
        for($i=0; $i < count($cfg_file); $i++) {
            $line = trim(str_replace("\t", " ", $cfg_file[$i]));
            $line = preg_replace('/\040{2,}/','#', $line);
            $line_parts = explode("#", $line);
            switch ($line_parts[0]){
                case "define timeperiod{":
                    $newObj = true;
                    $this->mainObj = new Timeperiod();
                    $tmpObj = new Timeperiod();
                    $this->out("--------------------------------------");
                    break;
                case "timeperiod_name":
                    $tmpObj->uuid = $line_parts[1];
                    $tmpObj->id =  $this->findIDfromUUID($this->mainObj, "Timeperiod", $line_parts[1]);
                    break;
                case "alias":
                    $tmpObj->name = trim($line_parts[1]);
                    $tmpObj->description = trim($line_parts[1]);
                    break;
                case "monday":
                    $this->addTimerange(1,$line_parts[1], $tmpObj->id);
                    break;
                case "tuesday":
                    $this->addTimerange(2,$line_parts[1], $tmpObj->id);
                    break;
                case "wednesday":
                    $this->addTimerange(3,$line_parts[1], $tmpObj->id);
                    break;
                case "thursday":
                    $this->addTimerange(4,$line_parts[1], $tmpObj->id);
                    break;
                case "friday":
                    $this->addTimerange(5,$line_parts[1], $tmpObj->id);
                    break;
                case "saturday":
                    $this->addTimerange(6,$line_parts[1], $tmpObj->id);
                    break;
                case "sunday":
                    $this->addTimerange(7,$line_parts[1], $tmpObj->id);
                    break;
                case "}":
                    $this->out($tmpObj->id);
                    $this->out($tmpObj->uuid);
                    $tmpObj = null;
                    $this->mainObj = null;
                    $this->out("--------------------------------------");
                    break;
            }
        }
        return $newObj;
    }

    private function addTimerange($day, $line, $timeperiod_id){
        $timerange = new Timerange();
        $timespan_parts = explode("-",$line);
        $timerange->timeperiod_id = $timeperiod_id;
        $timerange->day = $day;
        $timerange->start = $timespan_parts[0];
        $timerange->end = $timespan_parts[1];
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
            foreach ($command_names as $name) {
                //Werte für spezifisches commandargument setzen
                if ($commandargument["Commandargument"]["human_name"] == $name) {
                    $objekt_cmd_args->value = $command_params[$cnt + 1];
                    $objekt_cmd_args->id = $service_args[0][$type]["id"];
                    array_push($array, $objekt_cmd_args);
                    break;
                }
                $cnt++;
            }
        }
        return $uuid;
    }
}