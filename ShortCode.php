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
use Propel\Runtime\Propel;
use Propel\Runtime\Exception\PropelException;
use ShortCode\Model\ShortCodeQuery;
use Thelia\Core\Install\Database;
use Thelia\Module\BaseModule;

class ShortCode extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'shortcode';

    /**
     * Create the tables on a fresh install.
     *
     * The install script starts with DROP TABLE, so it only runs when the tables are missing.
     * The database is asked rather than an "is_initialized" flag in the module configuration:
     * a shop whose tables were created another way, or whose configuration was lost, would
     * otherwise have every short code wiped by a mere reactivation.
     */
    public function preActivation(?ConnectionInterface $con = null): bool
    {
        $con ??= Propel::getWriteConnection('TheliaMain');

        if (!$this->hasTable($con, 'short_code')) {
            (new Database($con))->insertSql(null, [__DIR__.'/Config/TheliaMain.sql']);
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

    private function hasTable(ConnectionInterface $con, string $table): bool
    {
        $statement = $con->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $statement->execute(['table' => $table]);

        return (int) $statement->fetchColumn() > 0;
    }
}
