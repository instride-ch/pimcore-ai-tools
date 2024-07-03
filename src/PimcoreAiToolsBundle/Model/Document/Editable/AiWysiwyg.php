<?php

declare(strict_types=1);

/**
 * instride AG.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright  Copyright (c) 2024 instride AG (https://instride.ch)
 */

namespace Instride\Bundle\PimcoreAiToolsBundle\Model\Document\Editable;

use Pimcore\Model\Document\Editable\Wysiwyg;
use Pimcore\Tool\Text;

final class AiWysiwyg extends Wysiwyg
{
    public function getType(): string
    {
        return 'ai_wysiwyg';
    }
}
