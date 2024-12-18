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

final class Version20240627132635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table for AiDefaultsConfiguration';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('pimcore_ai_tools_defaults_configuration')) {
            return;
        }

        $this->addSql('
            CREATE TABLE pimcore_ai_tools_defaults_configuration (id INT AUTO_INCREMENT NOT NULL, textProvider VARCHAR(255) NOT NULL, editableTextCreation LONGTEXT DEFAULT NULL, editableTextOptimization LONGTEXT DEFAULT NULL, editableTextCorrection LONGTEXT DEFAULT NULL, objectTextCreation LONGTEXT DEFAULT NULL, objectTextOptimization LONGTEXT DEFAULT NULL, objectTextCorrection LONGTEXT DEFAULT NULL, frontendTextCreation LONGTEXT DEFAULT NULL, frontendTextOptimization LONGTEXT DEFAULT NULL, frontendTextCorrection LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;
        ');
    }
}
