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

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\Free\Helper\Route;
use Alledia\OSDownloads\Free\Helper\SEF;
use Alledia\OSDownloads\MailingLists\Manager;

defined('_JEXEC') or die();

/**
 * Class Container
 *
 * @package OSDownloads
 *
 * @property Route   helperRoute
 * @property SEF     helperSEF
 * @property Manager mailingLists
 *
 * @method Route  getHelperRoute()
 * @method SEF    helperSEF()
 */
class Container extends \Pimple\Container
{
    /**
     * @var Container
     */
    protected static $instance;

    /**
     * The constructor. Allow to set the initial value for services.
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $values = array_merge(
            array(
                'helperRoute'    => null,
                'helperSanitize' => null,
            ),
            $values
        );

        parent::__construct($values);
    }

    /**
     * Magic method to get attributes.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this[$name])) {
            return $this[$name];
        }

        return null;
    }

    /**
     * Get the current instance
     *
     * @return Container
     */
    public static function getInstance()
    {
        if (!empty(static::$instance)) {
            return static::$instance;
        }

        // Instantiate the container
        static::$instance = new self;

        return static::$instance;
    }
}
