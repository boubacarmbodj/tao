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
 
 /* 
  * TO BE DEFINEED:
  * 
  * DATABASE_URL
  * DATABASE_LOGIN
  * DATABASE_PASS
  * DATABASE_NAME
  */
 
 if(!defined("DATABASE_NAME")){
 	echo "\nPlease configure me!\n";
	exit(1);
 }

define('SHOW_OUTPUT', false);
 
if(DATABASE_NAME == '') {
	$database = INSTALL_DATABASE_NAME; 
}
else {
	$database = DATABASE_NAME;
}

 mysql_query("SET NAMES 'utf8'");
 
 
 mysql_query("alter database ".$database." default CHARACTER SET utf8 COLLATE utf8_general_ci;");
 
  $counter = 0;
  $resultCounter = 0;
 
 $query = "show table status from ".$database;
 $result = mysql_query($query);
 while($row = mysql_fetch_array($result, MYSQL_BOTH)){
 	if($row['Collation'] != 'utf8_general_ci'){
 		$table = $row['Name'];
 		$alterQuery = "ALTER TABLE ".$table." CHARACTER SET utf8 COLLATE utf8_general_ci";
 		$counter++;
 		if(mysql_query($alterQuery)){
 			$resultCounter++;
 			if(SHOW_OUTPUT)
				print "\n".$table." change collation to utf8_general_ci";
 		}
 	}
 }
  if($counter > 0 && SHOW_OUTPUT){
 	print "\n".$resultCounter." / ".$counter." tables modified\n";
 }
 
 
 $counter = 0;
 $resultCounter = 0;
 
 $query = "SHOW TABLES";
 $result = mysql_query($query);
 while($row = mysql_fetch_array($result, MYSQL_BOTH)){
 	$table = $row[0];
 	$subQuery = "SHOW FULL COLUMNS FROM ".$table;
 	$subResult = mysql_query($subQuery);
 	while($subRow = mysql_fetch_array($subResult, MYSQL_BOTH)){
 		$type = $subRow['Type'];
 		$field = $subRow['Field'];
 		$collation = $subRow['Collation'];
 		if(preg_match("/^varchar/",$type) || preg_match("/^char/",$type) || preg_match("/^text/",$type) || preg_match("/text$/",$type)){
 			if($collation != 'utf8_general_ci'){
 				$counter++;
 				$alterQuery = "ALTER TABLE ".$table." MODIFY ".$field." ".$type." CHARACTER SET utf8 COLLATE utf8_general_ci";
 				if(mysql_query($alterQuery)){
 					$resultCounter++;
 					if(SHOW_OUTPUT)
						print "\n".$table.".".$field." : from ".$collation." to utf8_general_ci";
 				}
 			}
 		}
 	}
 }
 
 if($counter > 0 && SHOW_OUTPUT){
 	print "\n".$resultCounter." / ".$counter." fields modified\n";
 }
 
// mysql_close();
?>
