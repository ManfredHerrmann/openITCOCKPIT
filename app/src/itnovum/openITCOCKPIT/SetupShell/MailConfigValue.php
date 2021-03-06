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


namespace itnovum\openITCOCKPIT\SetupShell;


class MailConfigValue
{
    /**
     * @var String
     */
    private $value;

    /**
     * MailConfigValue constructor.
     * @param String $value
     */
    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return String
     */
    public function getValue(){
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueForConfig(){
        if($this->isEmpty()){
            return 'null';
        }
        return sprintf('"%s"', $this->value);
    }

    /**
     * @return bool
     */
    public function isEmpty(){
        return empty($this->value);
    }
}