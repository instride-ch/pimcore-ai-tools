<?php

declare(strict_types=1);

/**
 * instride.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright Copyright (c) 2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202400708142936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add permission entry for Pimcore AI-Tools';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('users_permission_definitions')) {
            return;
        }

        $this->addSql('
            INSERT INTO users_permission_definitions VALUES (\'pimcore_ai_tools\', \'Pimcore AI-Tools Bundle\');
        ');
    }
}
