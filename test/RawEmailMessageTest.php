<?php
// Copyright (c) 2010-2020 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>
//


namespace Combodo\iTop\Test\UnitTest\CombodoEmailSynchro;

use Combodo\iTop\Test\UnitTest\ItopTestCase;
use RawEmailMessage;

class RawEmailMessageTest extends ItopTestCase
{

	public function setUp()
	{
		parent::setUp();

		require_once(APPROOT . 'env-production/combodo-email-synchro/classes/rawemailmessage.class.inc.php');
	}

	/**
	 * @dataProvider EmailMessageProvider
	 */
	public function testEmailMessage($sFileName, $sComment, $bToIsEmpty)
	{
		$oEmail = RawEmailMessage::FromFile($sFileName);

		$sSubject = $oEmail->GetSubject();
		$this->assertNotEmpty($sSubject);


		$aSender = $oEmail->GetSender();
		$this->assertValidEmailCollection($aSender, 'Sender is valid');

		$aTo = $oEmail->GetTo();
		if ($bToIsEmpty)
		{
			$this->assertEmptyCollection($aTo, 'To is empty');
		}
		else
		{
			$this->assertValidEmailCollection($aTo, 'To is valid');
		}

		$aCc = $oEmail->GetCc();
		$this->assertValidEmailCollection($aCc, 'CC is valid');


		$sTextBody = $oEmail->GetTextBody();
		$sHTMLBody = $oEmail->GetHTMLBody();

		$this->assertFalse(($sTextBody == null) && ($sHTMLBody == null), 'No body found. (neither text not HTML).');


		$aAttachments = $oEmail->GetAttachments();
		foreach ($aAttachments as $aAttachment)
		{
			$this->assertArrayHasKey('filename', $aAttachment);
			$this->assertArrayHasKey('mimeType', $aAttachment);
			$this->assertArrayHasKey('content', $aAttachment);
		}

	}

	private function assertEmptyCollection($aEmails, $message = '')
	{
		foreach ($aEmails as $aAddr)
		{
			$this->assertEmpty($aAddr['email'], $message);
		}
	}

	private function assertValidEmailCollection($aEmails, $message = '')
	{
		foreach ($aEmails as $aAddr)
		{
			$this->assertArrayHasKey('email', $aAddr, $message);
			$this->assertValidEmail($aAddr['email'], $message);
		}
	}

	private function assertValidEmail($email, $message = '')
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
			'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$quoted_pair = '\\x5c[\\x00-\\x7f]';
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		$domain_ref = $atom;
		$sub_domain = "($domain_ref|$domain_literal)";
		$word = "($atom|$quoted_string)";
		$domain = "$sub_domain(\\x2e$sub_domain)*.?";
		$local_part = "$word(\\x2e$word)*";
		$addr_spec = "$local_part\\x40$domain";
		$this->assertRegExp("!^$addr_spec$!", $email, $message);
	}
	public function EmailMessageProvider()
	{
		parent::setUp();

		$aFiles = glob(APPROOT . 'env-production/combodo-email-synchro/test/emailsSample/*.eml');

		$aMetaData = array(
			'email_042.eml' => array(
				'bToIsEmpty' => true,
			),
			'email_045.eml' => array(
				'bToIsEmpty' => true,
			),
		);

		$aReturn = array();
		foreach ($aFiles as $sFile)
		{
			$sTestName = basename($sFile);

			$aReturn[$sTestName] = array(
				'sFile' => $sFile,
				'sComment' => isset($aMetaData[$sTestName]['sComment']) ? $aMetaData[$sTestName]['sComment'] : '',
				'bToIsEmpty' => isset($aMetaData[$sTestName]['bToIsEmpty']) ? $aMetaData[$sTestName]['bToIsEmpty'] : false,
			);
		}

		return $aReturn;
	}
}
