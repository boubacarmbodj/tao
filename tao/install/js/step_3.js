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
function onLoad(){
	
	// If we have available drivers, we set up
	// the list of db drivers.
	var availableDrivers = install.getData('available_drivers');
	
	if (availableDrivers != null && availableDrivers.length > 0){
		$databaseDriverElement = $('#database_driver');
		$databaseDriverElement.empty();
		
		for (var i in availableDrivers){
			$databaseDriverElement.append('<option value="' + availableDrivers[i] + '">' + getDriverLabel(availableDrivers[i]) + '</option>');
		}
	}
	
	install.onNextable = function(){
		$('#submitForm').removeClass('disable')
						.addClass('enabled')
						.attr('disabled', false);
		$('#submitForm').attr('value', 'Proceed next step');
	};
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
						.addClass('disabled')
						.attr('disabled', true);
		$('#submitForm').attr('value', 'Awaiting mandatory information');
	}
	
	$('form').bind('submit', function(){
		// set a spinner up.
		$database = $('#database');
		$database.css('visibility', 'visible');
		var spinner = new Spinner(getSpinnerOptions('small')).spin($database[0]);
		
		setTimeout(function(){ // Fake additional delay for user - 500ms.
			var host = install.getData('database_host');
			var user = install.getData('database_user');
			var password = install.getData('database_password');
			var driver = install.getData('database_driver');
			var database = install.getData('database_name');
			var overwrite = install.getData('database_overwrite');
			
			var check = {host: host,
						 user: user,
						 password: password,
						 driver: driver,
						 database: database,
						 overwrite: overwrite,
						 optional: false};
			
			install.checkDatabaseConnection(check, function(status, data){
				$database.css('visibility', 'hidden');
				spinner.stop();

				if (data.value.status == 'valid'){
					// Great! We could connect with the provided data.
					if (install.isNextable()){
						install.setTemplate('step_4');
					}
				}
				else if (data.value.status == 'invalid-noconnection'){
					// No connection established.
					var dsn = driver + '://' + user + '@' + host;
					var msg  = "Unable to connect to Relational Database Management ";
						msg += "System " + dsn + ".";
						
					if (data.value.message){
						msg	+= "\n\nError from server: " + data.value.message;
					}
					
					displayTaoError(msg);
				}
				else if (data.value.status == 'invalid-overwrite'){
					displayTaoError("A database with name '" + database + "' already exists. Check the corresponding check box to overwrite it.");
				}
				else if (data.value.status == 'invalid-nodriver'){
					displayTaoError("The database driver '" + driver + "' that should connect to your Relation Database Management System is not available on the server-side.");
				}
			});
		}, 500);
		
		return false;
	});
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
	// Initialize 'tao-input's.
	
	var firstValues = {};
	$('.tao-input').each(function(){
		$this = $(this);
		// Provide a data getter/setter for API handshake.
		install.getDataGetter(this);
		install.getDataSetter(this);
		
		// Get labelifed values from raw DOM.
		if ($this.prop('tagName').toLowerCase() == 'input' && $this.attr('type') == 'text'){
			firstValues[this.id] = this.getData();
		}
	});
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
	// Register inputs.
	$('.tao-input').each(function(){
		if (typeof(firstValues[this.id]) != 'undefined'){
			this.firstValue = firstValues[this.id];
		}
		
		switch (this.id){
			
			case 'database_host':
				install.getValidator(this, {dataType: 'dbhost'});
				validify(this);
			break;
			
			case 'database_user':
				install.getValidator(this, {dataType: 'string', min: 3, max: 30});
				validify(this);
			break;
			
			case 'database_password':
				// min = 0 to allow common root/[empty string] credential types.
				install.getValidator(this, {dataType: 'string', min: 0, max: 30, mandatory: false});
				validifyNotMandatory(this);
			break;
			
			case 'database_name':
				install.getValidator(this, {dataType: 'dbname'});
				validify(this);
			break;
			
			default:
				install.getValidator(this);
			break;
		}
		
		install.register(this);
		
		// When data is changed, tell the Install API.
		$(".tao-input[type=text], .tao-input[type=password], select").bind('keyup click change paste blur', function(event){
			install.stateChange();
		});
		
		$(".tao-input[type=checkbox]").bind("change", function(event){
			install.stateChange();
		});
		
		
	});
	
	// Populate form elements from API's data store.
	// (do not forget to restyle)
	$(install.populate()).each(function(){
		$(this).removeClass('helpTaoInputLabel');
	});
	
	initHelp();
}

function getDriverLabel(driverId){
	switch(driverId){
		case 'pdo_mysql':
			return 'MySQL or MariaDB';
		break;
		
		case 'pdo_pgsql':
			return 'PostgreSQL';
		break;
		
		case 'pdo_sqlsrv':
			return 'SQL Server'
		break;
		
		default:
			throw 'Unknown database driver (' + driverId + ').';
		break;
	}
}

function initHelp(){
	install.addHelp('hlp_database_driver', "Database drivers compliant with TAO are MySQL and PostgreSQL. Only drivers available on your web server are displayed in the list.");
	install.addHelp('hlp_database_host', 'The database host name is usually localhost.');
	install.addHelp('hlp_database_user', "The database user account that TAO will use to connect to the selected database system.");
	install.addHelp('hlp_database_password', "The database account password that TAO will you to connect to the selected database system. This field can be empty.");
	install.addHelp('hlp_database_name', "The name of the database to use by TAO in the selected database system. You can choose an existing database or let the installer create it for you.");
	install.addHelp('hlp_database_overwrite', "Tick this Checkbox if the database name you chose is already in use in your database system. Be careful, your database will be reset.");
	install.addHelp('hlp_sample_data', "Tick this Checkbox if you want to import sample data such as items, tests, ...");
}