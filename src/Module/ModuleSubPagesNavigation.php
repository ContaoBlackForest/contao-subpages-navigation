<?php
/**
 * Subpages Navigation Module
 *
 * PHP version 5
 *
 * @copyright  ContaoBlackForest <https://github.com/ContaoBlackforest/>
 * @author     Dominik Tomasi <dominik.tomasi@gmail.com>
 * @author     Sven Baumann <baumannsv@gmail.com>
 * @package    contao-subpages-navigation
 * @license    LGPL
 * @filesource
 */

namespace ContaoBlackForest\SubPagesNavigation\Module;


use Contao\Picture;


/**
 * Class ModuleSubPagesNavigation
 *
 * @package ContaoBlackForest\SubPagesNavigation\Module
 */
class ModuleSubPagesNavigation extends \Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_navigation';


    /**
     * @return string
     */
    public function generate()
    {

        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard =
                '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['subpages_navigation'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $strBuffer = parent::generate();

        return ($this->Template->items != '') ? $strBuffer : '';
    }

    /**
     *
     */
    public function compile()
    {
        //die(var_dump($GLOBALS['TL_DCA']['tl_module']));
        global $objPage;

        $this->showLevel = 1;
        $this->hardLimit = true;

        $this->Template->request = ampersand(\Environment::get('indexFreeRequest'));
        $this->Template->skipId = 'skipNavigation' . $this->id;
        $this->Template->skipNavigation = specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
        $this->Template->items = $this->renderNavigation($objPage->id, 1);
    }

    /**
     * Recursively compile the navigation menu and return it as HTML string
     * @param integer
     * @param integer
     * @param string
     * @param string
     * @return string
     */
    protected function renderNavigation($pid, $level=1, $host=null, $language=null)
    {
        // Get all active subpages
        $objSubpages = \PageModel::findPublishedSubpagesWithoutGuestsByPid($pid, $this->showHidden, $this instanceof \ModuleSitemap);

        if ($objSubpages === null)
        {
            return '';
        }

        $items = array();
        $groups = array();

        // Get all groups of the current front end user
        if (FE_USER_LOGGED_IN)
        {
            $this->import('FrontendUser', 'User');
            $groups = $this->User->groups;
        }

        // Layout template fallback
        if ($this->navigationTpl == '')
        {
            $this->navigationTpl = 'nav_default';
        }

        $objTemplate = new \FrontendTemplate($this->navigationTpl);

        $objTemplate->pid = $pid;
        $objTemplate->type = get_class($this);
        $objTemplate->cssID = $this->cssID; // see #4897
        $objTemplate->level = 'level_' . $level++;

        // Get page object
        global $objPage;

        // Browse subpages
        while ($objSubpages->next())
        {
            // Skip hidden sitemap pages
            if ($this instanceof \ModuleSitemap && $objSubpages->sitemap == 'map_never')
            {
                continue;
            }

            $subitems = '';
            $_groups = deserialize($objSubpages->groups);

            // Override the domain (see #3765)
            if ($host !== null)
            {
                $objSubpages->domain = $host;
            }

            // Do not show protected pages unless a back end or front end user is logged in
            if (!$objSubpages->protected || BE_USER_LOGGED_IN || (is_array($_groups) && count(array_intersect($_groups, $groups))) || $this->showProtected || ($this instanceof \ModuleSitemap && $objSubpages->sitemap == 'map_always'))
            {
                // Check whether there will be subpages
                if ($objSubpages->subpages > 0 && (!$this->showLevel || $this->showLevel >= $level || (!$this->hardLimit && ($objPage->id == $objSubpages->id || in_array($objPage->id, $this->Database->getChildRecords($objSubpages->id, 'tl_page'))))))
                {
                    $subitems = $this->renderNavigation($objSubpages->id, $level, $host, $language);
                }

                // Get href
                switch ($objSubpages->type)
                {
                    case 'redirect':
                        $href = $objSubpages->url;

                        if (strncasecmp($href, 'mailto:', 7) === 0)
                        {
                            $href = \String::encodeEmail($href);
                        }
                        break;

                    case 'forward':
                        if ($objSubpages->jumpTo)
                        {
                            $objNext = $objSubpages->getRelated('jumpTo');
                        }
                        else
                        {
                            $objNext = \PageModel::findFirstPublishedRegularByPid($objSubpages->id);
                        }

                        if ($objNext !== null)
                        {
                            // Hide the link if the target page is invisible
                            if (!$objNext->published || ($objNext->start != '' && $objNext->start > time()) || ($objNext->stop != '' && $objNext->stop < time()))
                            {
                                continue(2);
                            }

                            $strForceLang = null;
                            $objNext->loadDetails();

                            // Check the target page language (see #4706)
                            if (\Config::get('addLanguageToUrl'))
                            {
                                $strForceLang = $objNext->language;
                            }

                            $href = $this->generateFrontendUrl($objNext->row(), null, $strForceLang, true);
                            break;
                        }
                    // DO NOT ADD A break; STATEMENT

                    default:
                        if ($objSubpages->domain != '' && $objSubpages->domain != Environment::get('host'))
                        {
                            $objSubpages->current()->loadDetails();
                        }

                        $href = $this->generateFrontendUrl($objSubpages->row(), null, $language, true);
                        break;
                }


                // Insert navImage
                if ($objSubpages->navImage) {
                    $file = \FilesModel::findByUuid($objSubpages->navImage);
                    if ($file->path) {
                        $objSubpages->navImage = Picture::create($file->path, deserialize($this->imgSize,true))->getTemplateData();
                    }
                }

                $row = $objSubpages->row();
                $trail = in_array($objSubpages->id, $objPage->trail);

                // Active page
                if (($objPage->id == $objSubpages->id || $objSubpages->type == 'forward' && $objPage->id == $objSubpages->jumpTo) && !$this instanceof \ModuleSitemap && $href == \Environment::get('request'))
                {
                    // Mark active forward pages (see #4822)
                    $strClass = (($objSubpages->type == 'forward' && $objPage->id == $objSubpages->jumpTo) ? 'forward' . ($trail ? ' trail' : '') : 'active') . (($subitems != '') ? ' submenu' : '') . ($objSubpages->protected ? ' protected' : '') . (($objSubpages->cssClass != '') ? ' ' . $objSubpages->cssClass : '');

                    $row['isActive'] = true;
                    $row['isTrail'] = false;
                }

                // Regular page
                else
                {
                    $strClass = (($subitems != '') ? 'submenu' : '') . ($objSubpages->protected ? ' protected' : '') . ($trail ? ' trail' : '') . (($objSubpages->cssClass != '') ? ' ' . $objSubpages->cssClass : '');

                    // Mark pages on the same level (see #2419)
                    if ($objSubpages->pid == $objPage->pid)
                    {
                        $strClass .= ' sibling';
                    }

                    $row['isActive'] = false;
                    $row['isTrail'] = $trail;
                }

                $row['subitems'] = $subitems;
                $row['class'] = trim($strClass);
                $row['title'] = specialchars($objSubpages->title, true);
                $row['pageTitle'] = specialchars($objSubpages->pageTitle, true);
                $row['link'] = $objSubpages->title;
                $row['href'] = $href;
                $row['nofollow'] = (strncmp($objSubpages->robots, 'noindex', 7) === 0);
                $row['target'] = '';
                $row['description'] = str_replace(array("\n", "\r"), array(' ' , ''), $objSubpages->description);

                // Override the link target
                if ($objSubpages->type == 'redirect' && $objSubpages->target)
                {
                    $row['target'] = ($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"';
                }

                $items[] = $row;
            }
        }

        // Add classes first and last
        if (!empty($items))
        {
            $last = count($items) - 1;

            $items[0]['class'] = trim($items[0]['class'] . ' first');
            $items[$last]['class'] = trim($items[$last]['class'] . ' last');
        }

        $objTemplate->items = $items;
        return !empty($items) ? $objTemplate->parse() : '';
    }
}