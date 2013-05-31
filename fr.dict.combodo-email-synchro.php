<?php
// Copyright (C) 2010-2013 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU Lesser General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Dict::Add('FR FR', 'French', 'Français', array(
	// Dictionary entries go here
	'Class:MailInboxBase' => 'Boîte Mail',
	'Class:MailInboxBase+' => 'Source d\eMails',

	'Class:MailInboxBase/Attribute:server' => 'Serveur d\'eMails',
	'Class:MailInboxBase/Attribute:mailbox' => 'Boîte Mail (pour IMAP)',
	'Class:MailInboxBase/Attribute:login' => 'Identifiant',
	'Class:MailInboxBase/Attribute:password' => 'Mot de passe',
	'Class:MailInboxBase/Attribute:protocol' => 'Protocole',
	'Class:MailInboxBase/Attribute:protocol/Value:pop3' => 'POP3',
	'Class:MailInboxBase/Attribute:protocol/Value:imap' => 'IMAP',
	'Class:MailInboxBase/Attribute:port' => 'Port',
	'Class:MailInboxBase/Attribute:active' => 'Boîte Activée',
	'Class:MailInboxBase/Attribute:active/Value:yes' => 'Oui',
	'Class:MailInboxBase/Attribute:active/Value:no' => 'Non',


	'MailInbox:MailboxContent' => 'Contenu de la boîte mail',
	'MailInbox:EmptyMailbox' => 'La boîte mail est vide.',
	'MailInbox:ThereAre_X_Msg_Y_NewInTheMailbox' => 'Il y a %d eMail(s) dans la boîte (dont %d nouveaux).',
	'MailInbox:Status' => 'Etat',
	'MailInbox:Subject' => 'Objet',
	'MailInbox:From' => 'De',
	'MailInbox:RelatedTicket' => 'Ticket Lié',
	'MailInbox:Status/Processed' => 'Déjà Traité',
	'MailInbox:Status/New' => 'Nouveau',

	'MailInbox:Login/ServerMustBeUnique' => 'La combinaison Identifiant (%1$s) et Serveur (%2$s) est déjà utilisée par une Boîte Mail.',
	'MailInbox:Login/Server/MailboxMustBeUnique' => 'La combinaison Identifiant (%1$s), Serveur (%2$s) et boîte mail (%3$s) est déjà utilisée par une Boîte Mail.',

));