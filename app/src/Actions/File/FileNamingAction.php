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
use function Chevere\DataStructure\data;
use Chevere\Filesystem\Filename;
use Chevere\Filesystem\Interfaces\FilenameInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use function Chevere\String\randomString;
use Chevereto\Storage\Storage;

/**
 * Determines the best available target filename for the given storage, path and naming.
 */
class FileNamingAction extends Action
{
    public function run(
        int $id,
        #[ParameterAttribute(
            regex: '/^.+\.[a-zA-Z]+$/'
        )]
        string $name,
        Storage $storage,
        PathInterface $path,
        #[ParameterAttribute(
            regex: '/^original|random|mixed|id$/'
        )]
        string $naming = 'original',
    ): array {
        $encodedId = 'encoded';
        $file = new Filename($name);
        if ($naming === 'id') {
            return ['filename' => new Filename($encodedId . '.' . $file->extension())];
        }
        $name = $this->getName($naming, $file);
        // USE OWN INDEX, REQUIRE STORAGE ID PARAM
        while ($storage->adapter()->fileExists($path->getChild($name)->__toString())) {
            if ($naming === 'original') {
                $naming = 'mixed';
            }
            $name = $this->getName($naming, $file);
        }

        return data(
            filename: new Filename($name)
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                filename: objectParameter(
                    className: Filename::class
                ),
            );
    }

    public function getName(string $naming, FilenameInterface $filename): string
    {
        return match ($naming) {
            'original' => $filename->__toString(),
            'random' => $this->getRandomName($filename),
            'mixed' => $this->getMixedName($filename),
        };
    }

    private function getRandomName(FilenameInterface $filename): string
    {
        return randomString(32) . '.' . $filename->extension();
    }

    private function getMixedName(FilenameInterface $filename): string
    {
        $charsLength = 16;
        $chars = randomString($charsLength);
        $name = $filename->name();
        $nameLength = mb_strlen($name);
        $withExtensionLength = mb_strlen($filename->extension()) + 1;
        if ($nameLength + $charsLength > Filename::MAX_LENGTH_BYTES) {
            $chop = Filename::MAX_LENGTH_BYTES - $charsLength - $nameLength - $withExtensionLength;
            $name = mb_substr($name, 0, $chop);
        }

        return $name . $chars . '.' . $filename->extension();
    }
}
