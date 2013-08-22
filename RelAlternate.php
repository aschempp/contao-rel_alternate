<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class RelAlternate extends Frontend
{

    public function addHeader(Database_Result $objPage, Database_Result $objLayout, PageRegular $objPageRegular)
    {
        $arrExtensions = Config::getInstance()->getActiveModules();

        // Requires changelanguage extension to be installed
        if (!in_array('changelanguage', $arrExtensions)) {
            return;
        }

        global $objPage;

        $arrHeaders = array();
        $mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
        $objPages =  $this->Database->prepare("SELECT id FROM tl_page WHERE languageMain=? OR id=? AND published='1'")->execute($mainLanguageID, $mainLanguageID);

        while ($objPages->next()) {

            $objAlternate = $this->getPageDetails($objPages->id);

            $objRoot = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($objAlternate->rootId);

            if ($objRoot->languageRel == '') {
                continue;
            }

            $strUrl = $this->generateFrontendUrl($objAlternate->row(), null, $objRoot->language);

            if (!in_array('DomainLink', $arrExtensions) && $objAlternate->domain != $objPage->domain) {
                $strUrl = (Environment::getInstance()->ssl ? 'https://' : 'http://') . $objAlternate->domain . '/' . $strUrl;
            }

            $arrHeaders[] = '<link rel="alternate" hreflang="' . $objRoot->languageRel . '" href="' . $strUrl . '">';
        }

        if (!empty($arrHeaders)) {
            $GLOBALS['TL_HEAD'][] = implode("\n", $arrHeaders);
        }
    }
}
