<?php

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'combodo-email-synchro/3.3.0',
	array(
		// Identification
		'label' => 'Tickets synchronization via e-mail',
		'category' => 'business',
		// Setup
		'dependencies' => array(),
		'mandatory' => false,
		'visible' => true,
		'installer' => 'EmailSynchroInstaller',
		// Components
	'datamodel' => array(
		'classes/autoload.php',
		'model.combodo-email-synchro.php',
	),
	'dictionary' => array(
	),
	'data.struct' => array(
	),
	'data.sample' => array(
	),
	// Documentation
	'doc.manual_setup' => '', // No manual installation required
	'doc.more_information' => '', // None
	// Default settings
	'settings' => array(
		'notify_errors_to' => '', // mandatory to track errors not handled by the email processing module
		'notify_errors_from' => '', // mandatory as well (can be set at the same value as notify_errors_to)
		'debug' => false, // Set to true to turn on debugging
		'periodicity' => 30, // interval at which to check for incoming emails (in s)
		'body_parts_order' => 'text/html,text/plain', // Order in which to read the parts of the incoming emails
		'pop3_auth_option' => 'USER',
		'imap_options' => array('imap'),
		'maximum_email_size' => '10M', // Maximum allowed size for incoming emails
		'big_files_dir' => '',
		'exclude_attachment_types' => array('application/exe'), // Example: 'application/exe', 'application/x-winexe', 'application/msdos-windows'
		// Default tags to remove: array of tag_name => array of class names
		'html_tags_to_remove' => array(
			'blockquote' => array(),
			'div' => array('gmail_quote', 'moz-cite-prefix'),
			'pre' => array('moz-signature')
		),
		// Lines to be removed just above the 'new part' in a reply-to message... add your own patterns below
		'introductory_patterns' => array(
			'/^De : .+$/', // Outlook French
			'/^le .+ a écrit :$/i', // Thunderbird French
			'/^on .+ wrote:$/i', // Thunderbird English
			'|^[0-9]{4}/[0-9]{1,2}/[0-9]{1,2} .+:$|', // Gmail style
		),
		// Some patterns which delimit the previous message in case of a Reply
		// The "new" part of the message is the text before the pattern
		// Add your own multi-line patterns (use \\R for a line break)
		// These patterns depend on the mail client/server used... feel free to add your own discoveries to the list
		'multiline_delimiter_patterns' => array(
			'/\\RFrom: .+\\RSent: .+\\R/m', // Outlook English
			'/\\R_+\\R/m', // A whole line made only of underscore characters
			'/\\RDe : .+\\R\\R?Envoyé : /m', // Outlook French, HTML and rich text
			'/\\RDe : .+\\RDate d\'envoi : .+\\R/m', // Outlook French, plain text
			'/\\R-----Message d\'origine-----\\R/m',
		),
		'delimiter_patterns' => array(
			'/^>.*$/' => false, // Old fashioned mail clients: continue processing the lines, each of them is preceded by >
		),
		'use_message_id_as_uid' => false, // Do NOT change this unless you known what you are doing!!
		'images_minimum_size' => '100x20', // Images smaller that these dimensions will be ignored (signatures...)
		'images_maximum_size' => '', // Images bigger that these dimensions will be resized before uploading into iTop
		'recommended_max_allowed_packet' => 10*1024*1024, // MySQL parameter for attachments
	),
	)
);

if (!class_exists('EmailSynchroInstaller'))
{

	// Module installation handler
	//
	class EmailSynchroInstaller extends ModuleInstallerAPI
	{

		/**
		 * Handler called after the creation/update of the database schema
		 *
		 * @param $oConfiguration Config The new configuration of the application
		 * @param $sPreviousVersion string Previous version number of the module (empty string in case of first install)
		 * @param $sCurrentVersion string Current version number of the module
		 *
		 * @throws \ArchivedObjectException
		 * @throws \CoreException
		 * @throws \CoreUnexpectedValue
		 * @throws \DictExceptionMissingString
		 * @throws \MySQLException
		 * @throws \MySQLHasGoneAwayException
		 */
		public static function AfterDatabaseCreation(Config $oConfiguration, $sPreviousVersion, $sCurrentVersion)
		{
			// For each email sources, update email replicas by setting mailbox_path to source.mailbox where mailbox_path is null
			SetupPage::log_info("Updating email replicas to set their mailbox path.");

			// Preparing mailboxes search
			$oSearch = new DBObjectSearch('MailInboxBase');

			// Retrieving definition of attribute to update
			$sTableName = MetaModel::DBGetTable('EmailReplica');

			$UidlAttDef = MetaModel::GetAttributeDef('EmailReplica', 'uidl');
			$sUidlColName = $UidlAttDef->Get('sql');

			$oMailboxAttDef = MetaModel::GetAttributeDef('EmailReplica', 'mailbox_path');
			$sMailboxColName = $oMailboxAttDef->Get('sql');

			// Looping on inboxes to update
			$oSet = new DBObjectSet($oSearch);
			while ($oInbox = $oSet->Fetch())
			{
				$sUpdateQuery = "UPDATE $sTableName SET $sMailboxColName = " . CMDBSource::Quote($oInbox->Get('mailbox')) . " WHERE $sUidlColName LIKE " . CMDBSource::Quote($oInbox->Get('login') . '_%') . " AND $sMailboxColName IS NULL";
				SetupPage::log_info("Executing query: " . $sUpdateQuery);
				$iRet = CMDBSource::Query($sUpdateQuery); // Throws an exception in case of error
				SetupPage::log_info("Updated $iRet rows.");
			}
			
			// Workaround for BeforeWritingConfig() not having $sPreviousVersion
			if($sPreviousVersion != '' && version_compare($sPreviousVersion, '3.3.1', '<=')) {
				
				// In previous versions, these parameters were not named in a consistent way. Rename.
				$aSettings = array(
					'html_tags_to_remove', 
					'introductory_patterns',
					'multiline_delimiter_patterns',
					'delimiter_patterns'
				);
				
				$sTargetEnvironment = 'production';
				$sConfigFile = APPCONF.$sTargetEnvironment.'/'.ITOP_CONFIG_FILE;
				$oExistingConfig = new Config($sConfigFile);
				
				foreach($aSettings as $sSetting) {
					
					$aDeprecatedSettingValue = $oExistingConfig->GetModuleSetting('combodo-email-synchro', str_replace('_', '-', $sSetting), null);
					if($aDeprecatedSettingValue !== null) {
						$oExistingConfig->SetModuleSetting('combodo-email-synchro', $sSetting, $aDeprecatedSettingValue);
					}
					
				}
				
				// Update existing configuration PRIOR to iTop installation actually processing this.
				$oExistingConfig->WriteToFile();
				
			}
			
		}
		
		/**
		 * Handler called before the creation/update of the database schema
		 *
		 * @param $oConfiguration Config The new configuration of the application
		 *
		 * @returns \Config $oConfiguration The new configuration of the application
		 *
		 */
		public static function BeforeWritingConfig(Config $oConfiguration)
		{
			
			return $oConfiguration;
		}

	}

}
