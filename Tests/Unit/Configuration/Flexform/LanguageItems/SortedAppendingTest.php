<?php
namespace TYPO3\Beautyofcode\Tests\Unit\Configuration\Flexform\LanguageItems;

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Thomas Juhnke <typo3@van-tomas.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\Beautyofcode\Service\SettingsService;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Tests the sorted appending of configured brushes to the list of flexform items
 *
 * @author Thomas Juhnke <typo3@van-tomas.de>
 */
class SortedAppendingTest extends UnitTestCase {

	/**
	 *
	 * @test
	 */
	public function configuredBrushesAreAppendedSortedToTheReturnValue() {
		$this->markTestSkipped('This test can not run with UniqueAppendingTest at the moment due to a chaching issue.
			See #10 for more information. Move this markTestSkipped call to run this test.');

		/* @var $settingsServiceMock SettingsService|\PHPUnit_Framework_MockObject_MockObject */
		$settingsServiceMock = $this->getMock(SettingsService::class);
		$settingsServiceMock
			->expects($this->once())
			->method('getTypoScriptByPath')
			->with($this->equalTo('brushes'))
			->willReturn('Sql, Python, Php');

		/* @var $objectManagerMock \TYPO3\CMS\Extbase\Object\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
		$objectManagerMock = $this->getMock(ObjectManagerInterface::class);
		$objectManagerMock
			->expects($this->at(0))->method('get')
			->with(
				$this->equalTo('TYPO3\\Beautyofcode\\Service\\SettingsService'),
				$this->equalTo(1)
			)
			->will($this->returnValue($settingsServiceMock));

		/* @var $pageRepositoryMock \TYPO3\CMS\Frontend\Page\PageRepository */
		$pageRepositoryMock = $this->getMock('TYPO3\\CMS\\Frontend\\Page\\PageRepository');

		/* @var $templateServiceMock \TYPO3\CMS\Core\TypoScript\TemplateService */
		$templateServiceMock = $this->getMock('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');

		/* @var $highlighterConfigurationMock \TYPO3\Beautyofcode\Highlighter\Configuration\SyntaxHighlighter|\PHPUnit_Framework_MockObject_MockObject */
		$highlighterConfigurationMock = $this
			->getMockBuilder('TYPO3\\Beautyofcode\\Highlighter\\Configuration\\SyntaxHighlighter')
			->disableOriginalConstructor()
			->getMock();
		$highlighterConfigurationMock
			->expects($this->any())
			->method('hasBrushIdentifier')
			->will($this->returnValue(TRUE));
		$highlighterConfigurationMock
			->expects($this->any())
			->method('getBrushIdentifierAliasAndLabel')
			->will($this->returnValue(array('SQL / MySQL' => 'sql')));

		$sut = new \TYPO3\Beautyofcode\Configuration\Flexform\LanguageItems();
		$sut->injectObjectManager($objectManagerMock);
		$sut->injectPageRepository($pageRepositoryMock);
		$sut->injectTemplateService($templateServiceMock);
		$sut->injectHighlighterConfiguration($highlighterConfigurationMock);

		$templateServiceMock->setup = array(
			'plugin.' => array(
				'tx_beautyofcode.' => array(
					'settings.' => array(
						'brushes' => 'Sql, Python, Php',
					),
				),
			),
		);

		$configFromFlexform = array(
			'row' => array(
				'uid' => 1,
				'pid' => 1,
			),
			'items' => array(
				array(
					'Plain', // TCEforms: label
					'plain' // TCEforms: key
				),
			),
		);

		$newConfig = $sut->getConfiguredLanguages($configFromFlexform);

		$this->assertEquals('plain', $newConfig['items'][0][1]);
	}
}
