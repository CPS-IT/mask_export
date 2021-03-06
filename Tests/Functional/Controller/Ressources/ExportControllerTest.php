<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller\Ressources;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once __DIR__ . '/../AbstractExportControllerTestCase.php';

use CPSIT\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function ensureContentElementIconFromPreviewFolderInExport()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/ce_nested-content-elements.png', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../Fixtures/Templates/Preview/ce_nested-content-elements.png',
            $this->files['Resources/Public/Icons/Content/ce_nested-content-elements.png']
        );
    }

    /**
     * @test
     */
    public function extensionIconIsUsedAsDefaultContentElementIcon()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/ce_default-extension-icon.png', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../../../ext_icon.png',
            $this->files['Resources/Public/Icons/Content/ce_default-extension-icon.png']
        );
    }

    /**
     * @test
     */
    public function contentElementsHaveRegisteredIconIdentifier()
    {
        $maskConfiguration = json_decode(__DIR__ . '/../../Fixtures/mask.json');
        $this->installExtension();

        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        foreach ($maskConfiguration['tt_content']['elements'] as $key => $_) {
            $iconIdentifier = 'tx_maskexampleexport' . $key;

            $this->assertTrue($iconRegistry->isRegistered($iconIdentifier));
        }
    }

    /**
     * @test
     */
    public function itemsLabelsAreMovedToLocallangFile()
    {
        $this->assertArrayHasKey('Configuration/TCA/Overrides/tt_content.php', $this->files);
        $this->assertArrayHasKey('Resources/Private/Language/locallang_db.xlf', $this->files);

        $labels = [];
        preg_match_all(
            '#\d+\s*=>\s*array\s*\(\s*\d+\s*=>\s*\'LLL:EXT:([^\']+.I.[^\']+)\',\s*\),#',
            $this->files['Configuration/TCA/Overrides/tt_content.php'],
            $labels,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($labels);

        foreach ($labels as $label) {
            $id = array_pop(explode(':', $label[1]));

            $this->assertContains(
                '<trans-unit id="' . $id . '" xml:space="preserve">',
                $this->files['Resources/Private/Language/locallang_db.xlf']
            );
        }
    }
}
