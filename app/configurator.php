<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Config\Config;
use Chevereto\Config\EnabledConfig;
use Chevereto\Config\HostConfig;
use Chevereto\Config\LimitConfig;
use Chevereto\Config\SystemConfig;
use function Chevereto\Vars\env;

new Config(
    enabled: new EnabledConfig(
        phpPages: (bool) env()['CHEVERETO_ENABLE_PHP_PAGES'],
        updateCli: (bool) env()['CHEVERETO_ENABLE_UPDATE_CLI'],
        updateHttp: false,
        htaccessCheck: (bool) env()['CHEVERETO_ENABLE_HTACCESS_CHECK']
    ),
    host: new HostConfig(
        hostnamePath: env()['CHEVERETO_HOSTNAME_PATH'],
        hostname: env()['CHEVERETO_HOSTNAME'],
        isHttps: (bool) env()['CHEVERETO_HTTPS'],
    ),
    system: new SystemConfig(
        debugLevel: (int) env()['CHEVERETO_DEBUG_LEVEL'],
        errorLog: env()['CHEVERETO_ERROR_LOG'],
        imageFormatsAvailable: json_decode(
            env()['CHEVERETO_IMAGE_FORMATS_AVAILABLE'],
            true
        ),
        imageLibrary: env()['CHEVERETO_IMAGE_LIBRARY'],
        sessionSaveHandler: env()['CHEVERETO_SESSION_SAVE_HANDLER'],
        sessionSavePath: env()['CHEVERETO_SESSION_SAVE_PATH'],
    ),
    limit: new LimitConfig(
        invalidRequestsPerDay: 25
    )
);
