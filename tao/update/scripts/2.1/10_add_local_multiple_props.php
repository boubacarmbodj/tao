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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

$uris = array(
	'#i1296572584047457500',
	'#i1296572584036154400',
	'#i1296572584025114800',
	'#i1296572584014098100',
	'#i1296572584003629700',
	'#i1296572583092216600',
	'#i1296572583082488700',
	'#i1296572583063868700',
	'#i1296572583046907500',
	'#i1296572583030539200',
	'#i1296572583014612500',
	'#i1296572582098980300',
	'#i1296572582084290800',
	'#i1296572582069621400',
	'#i1296572582055098200',
	'#i1296572582040839900',
	'#i1296572582028170700',
	'#i1296572582015726700',
	'#i1296572582003777100',
	'#i1296572581090079200',
	'#i1296572581078999300',
	'#i1296572581068596000',
	'#i1296572581058611800',
	'#i1296572581048585700',
	'#i1296572581039416500',
	'#i1296572581029888600',
	'#i1296572581020936500',
	'#i1296572581012908200',
	'#i1296572581005570300'
);

$dbWrapper = core_kernel_classes_DbWrapper::singleton();
foreach($uris as $uri){
	$fullUri = LOCAL_NAMESPACE . $uri;
	$dbWrapper->execSql("INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
						(8, '$fullUri', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '',  'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]')");

}

?>