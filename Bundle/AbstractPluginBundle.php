<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\IntegrationsBundle\Bundle;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use MauticPlugin\IntegrationsBundle\Migration\Engine;

/**
 * Base Bundle class which should be extended by addon bundles.
 */
abstract class AbstractPluginBundle extends PluginBundleBase
{
    /**
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param array|null    $metadata
     * @param Schema|null   $installedSchema
     *
     * @throws Exception
     */
    public static function onPluginUpdate(Plugin $plugin, MauticFactory $factory, $metadata = null, ?Schema $installedSchema = null): void
    {
        $entityManager = $factory->getEntityManager();
        $tablePrefix   = $factory->getParameter('mautic.db_table_prefix');

        $migrationEngine = new Engine(
            $entityManager,
            $tablePrefix,
            __DIR__.'/../../'.$plugin->getBundle(),
            $plugin->getBundle()
        );

        if (method_exists(__CLASS__, 'installAllTablesIfMissing')) {
            static::installAllTablesIfMissing(
                $entityManager->getConnection()->getSchemaManager()->createSchema(),
                $tablePrefix,
                $factory,
                $metadata
            );
        }

        $migrationEngine->up();
    }

    /**
     * Returns the bundle name that this bundle overrides.
     *
     * Despite its name, this method does not imply any parent/child relationship
     * between the bundles, just a way to extend and override an existing
     * bundle.
     *
     * @return string The Bundle name it overrides or null if no parent
     */
    public function getParent()
    {
        return null;
    }
}
