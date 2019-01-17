<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ShortCode;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use ShortCode\Model\ShortCodeQuery;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class ShortCode extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'shortcode';

    public function preActivation(ConnectionInterface $con = null)
    {
        if (!$this->getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

            $this->setConfigValue('is_initialized', true);
        }

        return true;
    }

    /**
     * Create a new ShortCode
     * @param string $shortCodeName the name for call the ShortCode in template
     * @param string $eventName the name of the event dispatched when shortcode is in template
     * @throws PropelException
     */
    public static function createNewShortCodeIfNotExist($shortCodeName, $eventName)
    {
        if (null === ShortCodeQuery::create()->findOneByTag($shortCodeName)) {
            $shortCode = new \ShortCode\Model\ShortCode();
            $shortCode->setTag($shortCodeName)
                ->setEvent($eventName)
                ->setActive(1)
                ->save();
        }
    }

    /**
     * Active a ShortCode by his name
     * @param string $shortCodeName the name for call the ShortCode in template
     * @throws PropelException
     */
    public static function activateShortCode($shortCodeName)
    {
        $shortCode = ShortCodeQuery::create()
            ->filterByTag($shortCodeName)
            ->findOne();

        if (null !== $shortCode) {
            $shortCode->setActive(1)
                ->save();
        }
    }

    /**
     * Deactive a ShortCode by his name
     * @param string $shortCodeName the name for call the ShortCode in template
     * @throws PropelException
     */
    public static function deactivateShortCode($shortCodeName)
    {
        $shortCode = ShortCodeQuery::create()
            ->filterByTag($shortCodeName)
            ->findOne();

        if (null !== $shortCode) {
            $shortCode->setActive(0)
                ->save();
        }
    }
}
