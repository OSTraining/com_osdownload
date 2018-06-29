<?php
/**
 * @package   OSDownloadsContent
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('OSDOWNLOADS_LOADED')) {
    $includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
    if (is_file($includePath)) {
        require_once $includePath;
    }
}

if (defined('OSDOWNLOADS_LOADED')) {
    $baseClass = '\\Alledia\\OSDownloads\\%s\\Joomla\\Plugin\\Content';

    $pluginClass = sprintf($baseClass, 'Pro');
    if (class_exists($pluginClass)) {
        class PlgContentOsdownloads extends \Alledia\OSDownloads\Pro\Joomla\Plugin\Content
        {
        }

    } elseif (class_exists(sprintf($baseClass, 'Free'))) {
        class PlgContentOsdownloads extends \Alledia\OSDownloads\Free\Joomla\Plugin\Content
        {
        }

    } else {
        class PlgContentOsdownloads extends JPlugin
        {
            protected $autoloadLanguage = true;

            /**
             * PlgContentOsdownloads constructor.
             *
             * @param JEventDispatcher $subject
             * @param array            $config
             *
             * @throws Exception
             */
            public function __construct($subject, array $config = array())
            {
                parent::__construct($subject, $config);

                JFactory::getApplication()->enqueueMessage(
                    Jtext::sprintf('PLG_CONTENT_OSDOWNLOADS_NOT_INSTALLED', JText::_('PLG_CONTENT_OSDOWNLOADS'))
                );
            }
        }
    }
}
