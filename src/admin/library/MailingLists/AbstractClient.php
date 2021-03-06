<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\MailingLists;

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free;
use CategoriesTableCategory;
use JLog;
use JObservableInterface;
use JObserverInterface;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use JTable;
use OsdownloadsTableDocument;

defined('_JEXEC') or die();

abstract class AbstractClient implements JObserverInterface
{
    /**
     * @var Free\Joomla\Table\Email
     */
    protected $table = null;

    /**
     * @var Registry
     */
    protected static $params = null;

    /**
     * @var OsdownloadsTableDocument[]
     */
    protected static $documents = array();

    /**
     * @var CategoriesTableCategory[]
     */
    protected static $categories = array();

    public function __construct(JObservableInterface $table)
    {
        $table->attachObserver($this);
        $this->table = $table;
    }

    /**
     * @param JObservableInterface $observableObject The observable subject object
     * @param array                $params           Params for this observer
     *
     * @return  JObserverInterface
     */
    public static function createObserver(JObservableInterface $observableObject, $params = array())
    {
        $observer = new static($observableObject);

        return $observer;
    }

    /**
     * For customizing in subclasses. Prevents any access to a particular mailing list
     * if local dependencies are not available
     *
     * @return bool
     */
    public static function checkDependencies()
    {
        return true;
    }

    /**
     * For use in subclasses for display of plugin options on any form
     * other than the configuration form
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return true;
    }

    /**
     * @param int $documentId
     *
     * @return OsdownloadsTableDocument
     */
    protected function getDocument($documentId = null)
    {
        $documentId = (int)($documentId ?: $this->table->document_id);
        if (!isset(static::$documents[$documentId])) {
            /** @var OsdownloadsTableDocument $document */
            $document = JTable::getInstance('Document', 'OsdownloadsTable');
            $document->load($documentId);

            static::$documents[$documentId] = $document->id ? $document : false;
        }

        return static::$documents[$documentId] ?: null;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    protected function getUserByEmail($email)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__users')
            ->where('email = ' . $db->quote($email));

        if ($userId = (int)$db->setQuery($query)->loadResult()) {
            return Factory::getUser($userId);
        }

        return null;
    }

    /**
     * @param int $categoryId
     *
     * @return CategoriesTableCategory
     */
    protected function getCategory($categoryId)
    {
        $categoryId = (int)$categoryId;
        if ($categoryId && empty(static::$categories[$categoryId])) {
            /** @var CategoriesTableCategory $category */
            $category = JTable::getInstance('Category', 'JTable');
            $category->load($categoryId);

            if (!$category->params instanceof Registry) {
                $category->params = new Registry($category->params);
            }
            static::$categories[$categoryId] = $category;
        }

        if (!empty(static::$categories[$categoryId])) {
            return static::$categories[$categoryId];
        }

        return null;
    }

    /**
     * @return Registry
     */
    protected static function getParams()
    {
        if (static::$params === null) {
            static::$params = \JComponentHelper::getParams('com_osdownloads');
        }

        return static::$params;
    }

    /**
     * Get a parameter for a document.
     * Tracing back through category and global settings if needed
     *
     * @param int    $documentId
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getDocumentParam($documentId, $key, $default = null)
    {
        $document = $this->getDocument($documentId);
        $value    = $document->params->get($key);

        if (empty($value)) {
            // Try category lookup
            $category = $this->getCategory($document->cate_id);
            $value    = $category->params->get($key);

            if (empty($value)) {
                // Try global
                $value = $this->getParams()->get($key);
            }
        }

        return $value ?: $default;
    }

    /**
     * @param string $message
     * @param int    $level
     * @param string $category
     *
     * @return void
     */
    protected function logError($message, $level = JLog::ALERT, $category = null)
    {
        if (!$category) {
            $classParts = explode('\\', get_class($this));
            $category   = array_pop($classParts);
        }

        JLog::addLogger(array('text_file' => 'osdownloads.log.php'), JLog::ALL, $category);
        JLog::add($message, $level, $category);
    }
}
