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

namespace Instride\Bundle\PimcoreAiBundle\Service;

use eDiasoft\Midjourney\MidjourneyApiClient;

class MidjourneyService
{
    public function createImage()
    {
        $channel_id = $_ENV['MIDJOURNEY_CHANNEL_ID'];
        $authorization = $_ENV['MIDJOURNEY_AUTH_TOKEN'];

        $midjourney = new MidjourneyApiClient($channel_id, $authorization);

        return $midjourney->imagine('Elephant and a snake romantically having a diner')->send();
    }
}
