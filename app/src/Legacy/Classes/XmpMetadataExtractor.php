<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chevereto\Legacy\Classes;

use DOMDocument;
use JeroenDesloovere\XmpMetadataExtractor\XmpMetadataExtractor as Base;
use Throwable;

class XmpMetadataExtractor extends Base
{
    public function extractFromContent(string $content): array
    {
        try {
            $string = $this->getXmpXmlString($content);
            if ($string == '') {
                return [];
            }
            $doc = new DOMDocument();
            $doc->loadXML($string);
            $root = $doc->documentElement;
            $output = $this->convertDomNodeToArray($root);
            $output['@root'] = $root->tagName;

            return $output;
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function getXmpXmlString(string $content): string
    {
        $xmpDataStart = strpos($content, '<x:xmpmeta');
        if ($xmpDataStart === false) {
            return '';
        }
        $xmpDataEnd = (int) strpos($content, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        
        return substr($content, $xmpDataStart, $xmpLength + 12);
    }
}
