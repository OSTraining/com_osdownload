<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

class OSDownloadsViewDownloads extends JViewLegacy
{

    public function display($tpl = null)
    {
        $mainframe  = JFactory::getApplication();
        $params     = clone($mainframe->getParams('com_osdownloads'));
        $id         = $params->get("category_id", 1);
        $limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        if (JRequest::getVar("id")) {
            $id = JRequest::getVar("id");
        }

        $db = JFactory::getDBO();
        $query	= "SELECT documents.* , cate.published, cate.access AS cat_access
                   FROM `#__osdownloads_documents` documents
                   LEFT JOIN `#__categories` cate ON (documents.cate_id = cate.id AND cate.extension='com_osdownloads')
                   WHERE documents.cate_id = {$id} AND documents.published = 1 AND cate.published = 1
                   ORDER BY documents.ordering";

        $db->setQuery($query);
        $db->query();
        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();

        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if ((count($items) && !in_array($items[0]->cat_access, $groups)) || !isset($items)) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_CATEGORY_ISN't available"));

            return;
        }

        $this->buildPath($paths, $id);

        $db->setQuery("SELECT cate.*, (SELECT COUNT(id) FROM `#__osdownloads_documents` document WHERE document.cate_id = cate.id AND document.published = 1) AS total_doc FROM `#__categories` cate WHERE cate.extension='com_osdownloads' AND cate.published = 1 AND cate.parent_id = " . $id);
        $children = $db->loadObjectList();

        $this->assignRef("items", $items);
        $this->assignRef("paths", $paths);
        $this->assignRef("children", $children);
        $this->assignRef("pagination", $pagination);
        
        $this->_buildBreadcrumbs($paths);
        
        parent::display($tpl);
    }

    public function buildPath(& $paths, $id)
    {
        if (!$id) {
            return;
        }

        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM `#__categories` WHERE extension='com_osdownloads' AND id = " . $id);
        $cate = $db->loadObject();
        if ($cate) {
            $paths[] = $cate;
        }

        if ($cate && $cate->parent_id) {
            $this->buildPath($paths, $cate->parent_id);
        }
    }
    
    protected function _buildBreadcrumbs($paths){
        $app = JFactory::getApplication();
        $pathway = $app->getPathway();

        $countPaths =  count($paths) -1;
        for ($i = $countPaths; $i >= 0; $i--){
            $pathway->addItem($paths[$i]->title, JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}"."&Itemid=".JRequest::getVar("Itemid")));
        }
    }
}
