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

namespace Instride\Bundle\PimcoreAiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240619112835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table for AiEditableConfiguration';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('pimcore_ai_editable_configuration')) {
            return;
        }

        $this->addSql('
            CREATE TABLE pimcore_ai_editable_configuration (id INT AUTO_INCREMENT NOT NULL, areabrick VARCHAR(255) NOT NULL, editable VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, prompt LONGTEXT DEFAULT NULL, options LONGTEXT DEFAULT NULL, provider VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;
        ');
    }
}
