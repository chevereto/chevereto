<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Http\Controllers\Api\V4;

use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Http\Status;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Writer\StreamWriter;
use Chevereto\Exceptions\NotFoundException;
use Chevereto\PCRE;
use Chevereto\Tenants\Tenants;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\string;
use function Chevere\Standard\randomString;
use function Chevere\Writer\streamTemp;
use function Chevereto\Legacy\G\str_replace_last;
use function Chevereto\Legacy\runAppCommand;
use function Chevereto\Vars\env;

#[Response(
    new Status(201, fail: 404, conflict: 409),
)]
class TenantUserPasswordResetPatch extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(string $id): string
    {
        try {
            $tenant = $this->tenants->getTenant(tenantId: $id);
        } catch (NotFoundException) {
            throw new ControllerException(
                'Tenant not found',
                404
            );
        }
        $password = $this->bodyParsed()->optional('password')?->string()
            ?? randomString(16);
        $logger = new StreamWriter(streamTemp());
        $exit = runAppCommand(
            command: [
                '-C', 'password-reset',
                '-u', $this->bodyParsed()->required('username')->string(),
                '-x', $password,
            ],
            env: array_merge(env(), [
                'CHEVERETO_TENANT' => $tenant->id,
                'CHEVERETO_CACHE_KEY_PREFIX' => str_replace_last(
                    '_:',
                    '',
                    env()['CHEVERETO_CACHE_KEY_PREFIX']
                ),
                'CHEVERETO_DB_TABLE_PREFIX' => str_replace_last(
                    '_',
                    '',
                    env()['CHEVERETO_DB_TABLE_PREFIX']
                ),
            ]),
            isVerbose: true,
            logger: $logger
        );
        xr($exit);
        if ($exit === 0) {
            return $password;
        }

        throw new ControllerException(
            'Password reset failed',
            500
        );
    }

    public static function acceptBody(): ParameterInterface
    {
        return arrayp(
            username: string(PCRE::USER_USERNAME->value),
        )->withOptional(
            password: string(PCRE::USER_PASSWORD->value)
        );
    }
}
