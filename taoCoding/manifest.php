<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               
 * 
 */
?>
<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */
$extpath = dirname(__FILE__).DIRECTORY_SEPARATOR;
$taopath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'tao'.DIRECTORY_SEPARATOR;

return array(
	'name' => 'taoCoding',
	'description' => 'TAO Coding extensions http://www.tao.lu',
	'version' => '2.4',
	'author' => 'Open Assessment Technologies, CRP Henri Tudor',
	'dependencies' => array('taoResults', 'taoDelivery', 'taoQTI'),
	'constants' => array(
	 	"DIR_ACTIONS"			=> $extpath."actions".DIRECTORY_SEPARATOR,
	
		# views directory
		"DIR_VIEWS"				=> $extpath."views".DIRECTORY_SEPARATOR,
	
		# default module name
		'DEFAULT_MODULE_NAME'	=> 'Coding',
	
		#default action name
		'DEFAULT_ACTION_NAME'	=> 'index',
	
		#BASE PATH: the root path in the file system (usually the document root)
		'BASE_PATH'				=> $extpath,
	
		#BASE URL (usually the domain root)
		'BASE_URL'				=> ROOT_URL . 'taoCoding/',
	
		#BASE WWW the web resources path
		'BASE_WWW'				=> ROOT_URL . 'taoCoding/views/',
	 
	 	#TAO extension Paths
		'TAOBASE_WWW'			=> ROOT_URL  . 'tao/views/',
		'TAOVIEW_PATH'			=> $taopath.'views'.DIRECTORY_SEPARATOR,
		'TAO_TPL_PATH'			=> $taopath.'views'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
	)
);
?>
