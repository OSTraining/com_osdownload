<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\Framework\Factory;
use Joomla\Registry\Registry;
use JFactory;
use JLog;
use JText;

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
class SEF
{	
	/**
     * Returns the category id based on the file id.
     *
     * @param int $fileId
     *
     * @return int
     */
    public function getCategoryIdFromFile($fileId)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('cate_id')
            ->from('#__osdownloads_documents')
            ->where('id = ' . (int)$fileId);


        $catId = $db->setQuery($query)->loadResult();

        if (empty($catId)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $fileId,
                    'getCategoryIdFromFile'
                ),
                JLog::WARNING
            );
        }

        return $catId;
    }

    /**
     * Build the path to a category, considering the parent categories.
     *
     * @param array $categories
     * @param int   $catId
     */
    public function buildCategoriesPath(&$categories, $catId)
    {
        if (empty($catId)) {
            return;
        }

        $category = $this->getCategory($catId);

        if (!empty($category) && $category->alias !== 'root') {
            $categories[] = $category->alias;
        }

        if (!empty($category) && $category->parent_id) {
            $this->buildCategoriesPath($categories, $category->parent_id);
        }
    }

    /**
     * Append the category path to the segments.
     *
     * @param array $segments
     * @param int   $catId
     */
    public function appendCategoriesToSegments(&$segments, $catId)
    {
        // Append the categories before the alias of the file
        $categories = array();

        $this->buildCategoriesPath($categories, $catId);

        for ($i = count($categories) - 1; $i >= 0; $i--) {
            $segments[] = $categories[$i];
        }
    }

    /**
     * Returns the alias of a file based on the file id.
     *
     * @param int $id
     *
     * @return string
     */
    public function getFileAlias($id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote((int)$id));

        $alias = $db->setQuery($query)->loadResult();

        if (empty($alias)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $id,
                    'getFileAlias'
                ),
                JLog::WARNING
            );
        }

        return urlencode($alias);
    }

    /**
     * Returns the id of a file based on the file's alias.
     *
     * @param string $alias
     *
     * @return string
     */
    public function getFileIdFromAlias($alias)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__osdownloads_documents')
            ->where('alias = ' . $db->quote($alias));

        $id = $db->setQuery($query)->loadResult();

        if (empty($id)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $alias,
                    'getFileIdFromAlias'
                ),
                JLog::WARNING
            );
        }

        return $id;
    }

    /**
     * Returns the category as object based on the id.
     *
     * @param int $id
     *
     * @return stdClass
     */
    public function getCategory($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('id = ' . (int)$id);

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $id,
                    'getCategory'
                ),
                JLog::WARNING
            );
        }

        return $category;
    }

    /**
     * Returns the category as object based on the alias.
     *
     * @param array $aliases
     *
     * @return stdClass
     */
    public function getCategoryFromAlias(array $aliases)
    {
        $db = JFactory::getDBO();

        $level = count($aliases);
        $alias = array_pop($aliases);

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'level = ' . $level,
                    'alias = ' . $db->quote($alias)
                )
            );

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $alias,
                    'getCategoryFromAlias'
                ),
                JLog::WARNING
            );
        }

        return $category;
    }

    /**
     * Return the list of selected categories for the specific menu.
     *
     * @param int $itemId
     *
     * @return array
     */
    public function getCategoriesFromMenu($itemId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('params')
            ->from('#__menu')
            ->where('id = ' . $db->quote((int)$itemId));

        $params = $db->setQuery($query)->loadResult();
        $params = new Registry($params);

        $categories = $params->get('category_id', array());

        $list = array();
        if (!empty($categories)) {
            foreach ($categories as $categoryId) {
                $list[] = $this->getCategoryAlias($categoryId);
            }
        }

        return $list;
    }

    /**
     * Returns the category's alias based on the id.
     *
     * @param int $id
     *
     * @return string
     */
    public function getCategoryAlias($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'id = ' . (int)$id
                )
            );

        $alias = $db->setQuery($query)->loadResult();

        return urlencode($alias);
    }

    /**
     * Returns the category's id based on the alias.
     *
     * @param string $alias
     *
     * @return int
     */
    public function getCategoryId($alias)
    {
        $alias = urldecode($alias);

        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'alias = ' . $db->quote($alias)
                )
            );

        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Returns the id of the file set as the document in the menu
     *
     * @param int $itemId
     *
     * @return int|null
     */
    public function getFileIdFromMenuItemId($itemId)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('params')
            ->from('#__menu')
            ->where('id = ' . $db->quote($itemId));

        $params = $db->setQuery($query)->loadResult();

        if (!empty($params)) {
            $params = json_decode($params);

            if (is_object($params) && isset($params->document_id)) {
                return $params->document_id;
            }
        }

        return null;
    }
}
