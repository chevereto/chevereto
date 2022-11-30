<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class uses code that belongs or was taken from the following:
 *
 * David Soria Parra <sn_@gmx.net>
 * https://github.com/dsp/PHP-Gettext
 *
 * Jyxo, s.r.o.
 * https://github.com/jyxo/php/tree/master/Jyxo/Gettext
 *
 * WordPress
 * https://wordpress.org/
 */

/**
 * class.gettext.php
 * This class is a stand-alone implementation of gettext.
 * It works with .po and .mo files and saves the result in a cached static file (by default)
 */

namespace Chevereto\Legacy\G;

use Exception;
use Throwable;

/** @deprecated V4 */
class Gettext
{
    // Magic words in the MO header
    public const MO_MAGIC_1 = -569244523; //0xde120495

    public const MO_MAGIC_2 = -1794895138; //0x950412de

    // Cache stuff
    public const CACHE_FILE_SUFFIX = '.cache.php';

    protected static $default_options = ['cache' => true, 'cache_type' => 'file', 'cache_filepath' => null, 'cache_header' => true];

    protected $source_file;

    protected $parsed = false;

    public $translation_table = [];

    public $translation_plural = null;

    public $translation_header = null;

    private $is_cached = false;

    public function __construct($options = [])
    {
        $this->options = array_merge(static::$default_options, (array)$options);
        $this->source_file = $this->options['file'];

        if (file_exists($this->source_file) && !is_readable($this->source_file)) {
            throw new GettextException("Can't read source file", 600);
        }

        $file_extension = pathinfo($this->source_file, PATHINFO_EXTENSION);
        // Only allow MO and PO
        if (!in_array($file_extension, ['mo', 'po'])) {
            throw new GettextException('Invalid file source. This only works with .mo and .po files', 601);
        }

        $this->parse_method = strtoupper($file_extension);

        if ($this->options['cache']) {
            if ($this->options['cache_filepath']) {
                // Custom whatever filepath cache
                $this->cache_file = $this->options['cache_filepath'];
            } else {
                // Default cache filepath.cache.php
                $this->cache_file = $this->source_file . self::CACHE_FILE_SUFFIX;
            }
            if (!$this->getCache()) { // No cache was found
                $this->parseFile();
            }
        } else {
            $this->parseFile();
        }
    }

    /**
     * Return a translated string
     *
     * If the translation is not found, the original message will be returned.
     *
     */
    public function gettext(string $msg)
    {
        if (empty($msg)) {
            return null;
        }
        if (!$this->parsed) {
            $this->parseFile();
        }

        if ($this->mustFixQuotes()) {
            $msg = $this->fixQuotes($msg, 'escape');
        }

        $translated = $msg;

        if (array_key_exists($msg, $this->translation_table)) {
            $translated = $this->translation_table[$msg][0] ?? null;
            $translated = !empty($translated) ? $translated : $msg;
        }

        if ($this->mustFixQuotes()) {
            $translated = $this->fixQuotes($translated, 'unescape');
        }

        return $translated;
    }

    /**
     * Return a translated string in it's plural form
     *
     * Returns the given $count (e.g second, third,...) plural form of the
     * given string. If the id is not found and $num == 1 $msg is returned,
     * otherwise $msg_plural
     *
     * @param String $msg The message to search for
     * @param string $msg_plural A fallback plural form
     * @param integer $count Which plural form
     *
     */
    public function ngettext($msg, $msg_plural, $count = 0)
    {
        if (empty($msg) or empty($msg_plural) or !is_numeric($count)) {
            return $msg;
        }
        if (!$this->parsed) {
            $this->parseFile();
        }

        if ($this->mustFixQuotes()) {
            $msg = $this->fixQuotes($msg, 'escape');
            $msg_plural = $this->fixQuotes($msg_plural, 'escape');
        }

        $translated = $count == 1 ? $msg : $msg_plural; // Failover

        if (array_key_exists($msg, $this->translation_table)) {
            $plural_index = $this->getPluralIndex($count);
            $index_id = $plural_index !== false ? $plural_index : ($count - 1);
            $table = $this->translation_table[$msg];
            if (array_key_exists($index_id, $table)) {
                $translated = $table[$index_id];
            }
        }

        if ($this->mustFixQuotes()) {
            $translated = $this->fixQuotes($translated, 'unescape');
        }

        return $translated;
    }

    /**
     * Parse the source file
     * If cache is enabled it will try to cache the result
     */
    private function parseFile()
    {
        $parseFn = 'parse' . $this->parse_method . 'File';
        $this->$parseFn();
        $this->parsed = true;
        if ($this->options['cache']) {
            $this->cache('file');
        }
    }

    /**
     * Parse the MO file header and returns the table
     * offsets as described in the file header.
     *
     * If an exception occurred, null is returned. This is intentionally
     * as we need to get close to ext/gettext behaviour.
     *
     * @param resource $fp The open file handler to the MO file
     *
     * @return array offset
     */
    private function parseMOHeader($fp)
    {
        $data = fread($fp, 8);
        if (!$data) {
            throw new GettextException("Can't fread(8) file for reading", 602);
        }
        $header = unpack('lmagic/lrevision', $data);
        if (self::MO_MAGIC_1 != $header['magic'] && self::MO_MAGIC_2 != $header['magic']) {
            return null;
        }
        if (0 != $header['revision']) {
            return null;
        }
        $data = fread($fp, 4 * 5);
        if (!$data) {
            throw new GettextException("Can't fread(4 * 5) file for reading", 603);
        }

        return unpack('lnum_strings/lorig_offset/' . 'ltrans_offset/lhash_size/lhash_offset', $data);
    }

    /**
     * Parse and returns the string offsets in a a table. Two table can be found in
     * a mo file. The table with the translations and the table with the original
     * strings. Both contain offsets to the strings in the file.
     *
     * If an exception occurred, null is returned. This is intentionally
     * as we need to get close to ext/gettext behaviour.
     *
     * @param resource	$fp     The open file handler to the MO file
     * @param int		$offset The offset to the table that should be parsed
     * @param int   	$num    The number of strings to parse
     *
     * @return Array of offsets
     */
    private function parseMOTableOffset($fp, $offset, $num)
    {
        if (fseek($fp, $offset, SEEK_SET) < 0) {
            return null;
        }
        $table = [];
        for ($i = 0; $i < $num; $i++) {
            $data = fread($fp, 8);
            $table[] = unpack('lsize/loffset', $data);
        }

        return $table;
    }

    /**
     * Parse a string as referenced by an table. Returns an
     * array with the actual string.
     *
     * @param resource $fp The open file handler to the MO fie
     * @param array	$entry The entry as parsed by parseMOTableOffset()
     *
     */
    private function parseMOEntry($fp, $entry): ?string
    {
        if (fseek($fp, $entry['offset'], SEEK_SET) < 0) {
            return null;
        }
        if ($entry['size'] > 0) {
            return fread($fp, $entry['size']) ?: null;
        }

        return null;
    }

    /**
     * Parse the plural data found in the language
     *
     * @param string $header with nplurals and plural declaration
     */
    private function parsePluralData($header)
    {
        // Base english-like plural languages
        $nplurals = 2;
        $formula = '(n != 1)';
        // Detect plural data. If nothing found then use general plural handling
        if (preg_match('/\s*nplurals\s*\=\s*(\d+)\s*\;\s*plural\s*\=\s*(\({0,1}.*\){0,1})\s*\;/', $header, $matches)) {
            $nplurals = (int) $matches[1];
            if (preg_match('/^([!n\=\<\>\&\|\?\:%\s\(\)\d]+)$/', (string) $matches[2]) === 1) {
                $formula = $matches[2];
            }
        }

        // Fix the plural formula
        $formula = $this->parenthesizePluralFormula($formula);

        // Generate the translation_plural array
        $function = str_replace('n', '$n', $formula);

        // Stock everything
        $this->translation_plural = [
            'nplurals' => $nplurals,
            'function' => $function,
        ];
    }

    /**
     * Adds parentheses to the inner parts of ternary operators in
     * plural formulas, because PHP evaluates ternary operators from left to right
     *
     * @param string $formula the expression without parentheses
     * @return string the formula with parentheses added
     */
    private function parenthesizePluralFormula($formula)
    {
        $formula .= ';';
        $return = '';
        $depth = 0;
        for ($i = 0; $i < strlen($formula); ++$i) {
            $char = $formula[$i];
            switch ($char) {
                case '?':
                    $return .= ' ? (';
                    $depth++;

                    break;
                case ':':
                    $return .= ') : (';

                    break;
                case ';':
                    $return .= str_repeat(')', $depth) . ';';
                    $depth = 0;

                    break;
                default:
                    $return .= $char;
            }
        }
        $return = trim(rtrim($return, ';')); // Cleaning
        $return = preg_replace('/\s+/S', ' ', $return); // Extra spaces

        return str_replace('( ', '(', str_replace(' )', ')', $return)); // Remove extra space around ()
    }

    /**
     * Get plural index
     *
     * @param int $count msg count
     * @return int plural index
     */
    public function getPluralIndex($count)
    {
        if (!is_callable($this->translation_plural['callable'] ?? null)) {
            // So, this is how you interpeter this thing
            $function = $this->translation_plural['function'];
            $nplurals = $this->translation_plural['nplurals'];
            $evil = "\$callable = function(\$n) {\$index = (int)$function; return \$index < $nplurals ? \$index : ($nplurals - 1);};";
            eval($evil);
            /** @var callable $callable */
            $this->translation_plural['callable'] = $callable;
        }

        return call_user_func($this->translation_plural['callable'], $count);
    }

    private function parseHeader($header)
    {
        $headerTable = [];
        $lines = array_map('trim', explode("\n", $header));
        foreach ($lines as $line) {
            if (starts_with('msgid', $line) or starts_with('msgstr', $line)) {
                continue;
            }
            $line = preg_replace('#\"(.*)\"#', '$1', $line);
            $line = rtrim($line, '\n');
            $parts = explode(':', $line, 2);
            if (!isset($parts[1])) {
                continue;
            } // Skip empty keys
            $headerTable[trim($parts[0])] = trim($parts[1]);
        }

        return $headerTable;
    }

    /**
     * Parse a PO entry chunk
     * @param string $chunk
     *
     * @return Array of translation table
     */
    private function parsePOEntry($chunk)
    {
        $chunks = explode("\n", $chunk);
        foreach ($chunks as $chunk) {
            if (starts_with('#', $chunk) or is_null($chunk)) {
                continue;
            }
            if (is_null($this->translation_plural) and starts_with('"Plural-Forms:', $chunk)) {
                $this->parsePluralData($chunk);
            }
            if (preg_match('/^msgid "(.*)"/', $chunk, $matches)) {
                $msgid = $matches[1];
            } elseif (preg_match('/^msgstr "(.*)"/', $chunk, $matches)) {
                $msgstr = $matches[1];
            } elseif (preg_match('/^#~ msgid "(.*)"/', $chunk, $matches)) {
                $msgid = $matches[1];
            } elseif (preg_match('/^#~ msgstr "(.*)"/', $chunk, $matches)) {
                $msgstr = $matches[1];
            } elseif (preg_match('/^msgstr\[([0-9])+\] "(.*)"/', $chunk, $matches)) {
                if ($matches[2] == '') {
                    continue;
                }
                if (!is_array($msgstr ?? null)) {
                    $msgstr = [];
                }
                $msgstr[$matches[1]] = $matches[2];
            }
        }
        $msgstr ??= null;
        if ($msgstr == '') {
            $msgstr = null;
        }
        if (empty($msgid)) {
            return null;
        } else {
            return [
                'msgid' => $msgid,
                'msgstr' => is_null($msgstr) ? null : (array)$msgstr
            ];
        }
    }

    /**
     * Parse binary .mo file
     */
    private function parseMOFile()
    {
        $filesize = filesize($this->source_file);
        if ($filesize < 4 * 7) {
            return;
        }

        $fp = @fopen($this->source_file, 'rb');
        if (!$fp) {
            throw new GettextException("Can't fopen file for reading", 600);
        }

        $offsets = $this->parseMOHeader($fp);

        if (null == $offsets || $filesize < 4 * ($offsets['num_strings'] + 7)) {
            fclose($fp);

            return;
        }

        $transTable = [];
        $table = $this->parseMOTableOffset($fp, $offsets['trans_offset'], $offsets['num_strings']);
        if (null == $table) {
            fclose($fp);

            return;
        }

        foreach ($table as $idx => $entry) {
            $transTable[$idx] = $this->parseMOEntry($fp, $entry);
        }

        $this->translation_header = $this->parseHeader(reset($transTable));

        // Parse plural data
        $this->parsePluralData($this->translation_header['Plural-Forms']);

        $table = $this->parseMOTableOffset($fp, $offsets['orig_offset'], $offsets['num_strings']);

        foreach ($table as $idx => $entry) {
            $entry = $this->parseMOEntry($fp, $entry);
            $formes = explode(chr(0), $entry);
            $translation = explode(chr(0), $transTable[$idx]);
            foreach ($formes as $form) {
                if (empty($form)) {
                    continue;
                }
                $this->translation_table[$form] = $translation;
            }
        }

        fclose($fp);
    }

    /**
     * Parse text based .po file
     */
    private function parsePOFile()
    {
        $linenumber = 0;
        $chunks = [];
        $file = file($this->source_file);
        if (!$file) {
            throw new GettextException("Can't read file into an array", 604);
        }
        foreach ($file as $line) {
            if ($line == "\n" or $line == "\r\n") {
                ++$linenumber;
            } else {
                if (!array_key_exists($linenumber, $chunks)) {
                    $chunks[$linenumber] = '';
                }
                $chunks[$linenumber] .= $line;
            }
        }
        $this->translation_header = $this->parseHeader(reset($chunks));
        foreach ($chunks as $chunk) {
            $entry = $this->parsePOEntry($chunk);
            if (!isset($entry['msgid']) or !isset($entry['msgstr'])) {
                continue;
            }
            $this->translation_table[$entry['msgid']] = $entry['msgstr'];
        }
    }

    /**
     * Get cached results (cached file)
     *
     * @return bool cache status
     */
    private function getCache()
    {
        try {
            is_readable($this->cache_file);
            // Outdated cache?
            $source_mtime = filemtime($this->source_file);
            $cache_mtime = file_exists($this->cache_file) ? filemtime($this->cache_file) : 0;
            if ($source_mtime and $cache_mtime and $source_mtime > $cache_mtime) {
                return false;
            }

            try {
                include_once $this->cache_file;
            } catch (Throwable $e) {
                return false;
            }
            if (isset($translation_table)) {
                $this->translation_table = $translation_table;
                if (isset($translation_plural)) {
                    $this->translation_plural = $translation_plural;
                }
                if (isset($translation_header)) {
                    $this->translation_header = $translation_header;
                }
                $this->is_cached = true;
                $this->parsed = true;

                return true;
            }
        } catch (Throwable $e) {
        }

        $this->is_cached = false;

        return false;
    }

    /**
     * Cache the translation results into a file
     */
    private function cache()
    {
        is_dir(dirname($this->cache_file));
        $fh = fopen($this->cache_file, 'w');
        if ($fh === false) {
            throw new GettextException("Can't fopen cache file for writing", 601);
        }
        $contents = '<?php' . "\n";
        if ($this->options['cache_header']) {
            if (!is_null($this->translation_header)) {
                $contents .= '$translation_header = ' . var_export($this->translation_header, true) . ';' . "\n";
            }
            if (!is_null($this->translation_plural)) {
                $translation_plural = $this->translation_plural;
                unset($translation_plural['callable']); // Don't cache the callable reference
                $contents .= '$translation_plural = ' . var_export($translation_plural, true) . ';' . "\n";
            }
        }
        $contents .= '$translation_table = [';
        foreach ($this->translation_table as $k => $v) {
            $k = $this->parse_method == 'PO' ? $k : $this->fixQuotes($k, 'escape');
            $contents .= "\n" . '	"' . $k . '" => [';
            foreach ($v as $kk => $vv) {
                $kk = $this->parse_method == 'PO' ? $kk : $this->fixQuotes($kk, 'escape');
                $vv = $this->parse_method == 'PO' ? $vv : $this->fixQuotes($vv, 'escape');
                $contents .= "\n" . '		' . $kk . ' => "' . $vv . '",';
            }
            $contents .= "\n" . '	],';
        }
        $contents .= "\n" . '];' . "\n" . '?>';
        if (!fwrite($fh, $contents)) {
            throw new GettextException("Can't save translation results to cache file", 602);
        }

        try {
            touch($this->source_file);
        } catch (Throwable $e) {
            // Shhh
        }
        fclose($fh);
    }

    private function fixQuotes($msg, $action = null)
    {
        if ($this->is_cached) {
            return $msg;
        }
        switch ($action) {
            case 'escape':
                $msg = str_replace('"', '\"', $msg);

            break;
            case 'unescape':
                $msg = str_replace('\"', '"', $msg);

            break;
        }

        return $msg;
    }

    private function mustFixQuotes()
    {
        return $this->is_cached or $this->parse_method == 'PO';
    }
}

class GettextException extends Exception
{
}
