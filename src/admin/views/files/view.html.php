<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

use Alledia\Installer\Extension\Licensed;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;

defined('_JEXEC') or die();

class OSDownloadsViewFiles extends JViewLegacy
{
    /**
     * @var string
     */
    protected $sidebar = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @var Licensed
     */
    protected $extension = null;

    /**
     * @var object[]
     */
    protected $items = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $model = JModelLegacy::getInstance('Items', 'OSDownloadsModel');
        $this->setModel($model, true);
    }

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var OSDownloadsModelItems $model */
        $model = $this->getModel();

        $this->state      = $model->getState();
        $this->pagination = $model->getPagination();

        $this->extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();

        $db    = JFactory::getDBO();
        $query = $model->getItemsQuery();

        $db->setQuery($query, $this->pagination->limitstart, $this->pagination->limit);
        $this->items = $db->loadObjectList();

        foreach ($this->items as &$item) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
                $item->agreementLink = JRoute::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));
            }
        }

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(
            JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_FILES'),
            'file-2 osdownloads-files'
        );

        JToolBarHelper::custom('file', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
        JToolBarHelper::custom('file', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        JToolBarHelper::custom('files.delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
        JToolBarHelper::divider();
        JToolBarHelper::custom('files.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        JToolBarHelper::custom('files.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_osdownloads', '450');
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'doc.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
            'doc.published'  => JText::_('COM_OSDOWNLOADS_PUBLISHED'),
            'doc.name'       => JText::_('COM_OSDOWNLOADS_NAME'),
            'doc.access'     => JText::_('COM_OSDOWNLOADS_ACCESS'),
            'doc.downloaded' => JText::_('COM_OSDOWNLOADS_DOWNLOADED'),
            'doc.id'         => JText::_('COM_OSDOWNLOADS_ID')
        );
    }
}
