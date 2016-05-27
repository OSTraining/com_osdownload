<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Model;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Model\Base as BaseModel;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Helper;

class Email extends BaseModel
{
    protected function prepareRow(&$row)
    {
        // Method to allow inheritance
    }

    public function insert($email, $documentId)
    {
        $component = FreeComponentSite::getInstance();
        $row = $component->getTable('Email');

        if (Helper::validateEmail($email)) {
            $row->email = $email;

            $row->document_id = (int) $documentId;
            $row->downloaded_date = Factory::getDate()->toSQL();

            $this->prepareRow($row);

            $row->store();

            return $row;
        }

        return false;
    }

    public function getEmail($emailId)
    {
        $component = FreeComponentSite::getInstance();
        $row = $component->getTable('Email');

        $row->load((int) $emailId);

        return $row;
    }
}
