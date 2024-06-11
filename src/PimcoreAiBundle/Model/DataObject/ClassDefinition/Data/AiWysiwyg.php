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


namespace Instride\Bundle\PimcoreAiBundle\Model\DataObject\ClassDefinition\Data;

use Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg;

class AiWysiwyg extends Wysiwyg
{
    public function getFieldType(): string
    {
        return 'aiWysiwyg';
    }
}
