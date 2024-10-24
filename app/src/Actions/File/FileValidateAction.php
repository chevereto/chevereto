<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\File;

use Chevere\Action\Action;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Interfaces\ParametersInterface;
use InvalidArgumentException;
use Throwable;
use function Chevere\DataStructure\data;
use function Chevere\Message\message;
use function Chevere\Parameter\int;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\string;
use function Safe\filesize;
use function Safe\md5_file;
use function Safe\mime_content_type;

/**
 * Validate file type and its size.
 */
class FileValidateAction extends Action
{
    private array $mimes = [];

    private int $maxBytes = 0;

    private int $minBytes = 0;

    public function run(
        #[StringAttr(
            '/^([\w]+\/[\w\-\+\.]+)+(,([\w]+\/[\w\-\+\.]+))*$/',
            'Comma-separated list of allowed mime-types.'
        )]
        string $mimes,
        string $filepath,
        int $maxBytes = 0,
        int $minBytes = 0,
    ): array {
        $this->mimes = explode(',', $mimes);
        $this->minBytes = $minBytes;
        $this->maxBytes = $maxBytes;
        $bytes = $this->assertGetFileBytes($filepath);
        $this->assertMaxBytes($bytes);
        $this->assertMinBytes($bytes);
        $mime = mime_content_type($filepath);
        $this->assertMime($mime);

        return data(
            bytes: $bytes,
            mime: $mime,
            md5: md5_file($filepath),
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                bytes : int(),
                mime : string(),
                md5 : string(),
            );
    }

    /**
     * @codeCoverageIgnore
     */
    private function assertGetFileBytes(string $filepath): int
    {
        try {
            return filesize($filepath);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                message($e->getMessage()),
                1000
            );
        }
    }

    private function assertMinBytes(int $bytes): void
    {
        if ($this->minBytes === 0) {
            return;
        }
        if ($bytes < $this->minBytes) {
            throw new InvalidArgumentException(
                message(
                    "Filesize (`%fileSize%`) doesn't meet the minimum bytes required (`%required%`)",
                    fileSize: (string) ($bytes . ' B'),
                    required: (string) ($this->minBytes . ' B'),
                ),
                1001
            );
        }
    }

    private function assertMaxBytes(int $bytes): void
    {
        if ($this->maxBytes === 0) {
            return;
        }
        if ($bytes > $this->maxBytes) {
            throw new InvalidArgumentException(
                message(
                    'Filesize (`%fileSize%`) exceeds the maximum bytes allowed (`%allowed%`)',
                    fileSize: (string) ($bytes . ' B'),
                    allowed: (string) ($this->maxBytes . ' B'),
                ),
                1002
            );
        }
    }

    private function assertMime(string $mime): void
    {
        if (! in_array($mime, $this->mimes, true)) {
            throw new InvalidArgumentException(
                message(
                    'File mime-type `%type%` is not allowed (allows `%allowed%`)',
                    type: $mime,
                    allowed: implode(', ', $this->mimes),
                ),
                1004
            );
        }
    }
}
