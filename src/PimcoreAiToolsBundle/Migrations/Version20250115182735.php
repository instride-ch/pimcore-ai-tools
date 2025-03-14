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

final class Version20250115182735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table for AiTranslationObjectConfiguration';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('pimcore_ai_tools_translation_object_configuration')) {
            return;
        }

        $this->addSql('
        CREATE TABLE pimcore_ai_tools_translation_object_configuration (
            id INT AUTO_INCREMENT NOT NULL, 
            className VARCHAR(255) NOT NULL, 
            fields LONGTEXT NOT NULL, 
            standardLanguage VARCHAR(255) NOT NULL, 
            provider VARCHAR(255) DEFAULT NULL, 
            UNIQUE KEY unique_class_name (className),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;
    ');
    }
}
