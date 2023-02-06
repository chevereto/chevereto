<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\G;

 use Chevereto\Config\Config;
use function Chevereto\Vars\env;
use function Chevereto\Vars\server;
use Composer\CaBundle\CaBundle;
use CurlHandle;
use DateInterval;
use ErrorException;
use Exception;
use GdImage;
use LogicException;
use function Safe\curl_exec;
use Throwable;

/**
 * ROUTE HELPERS
 * ---------------------------------------------------------------------.
 */

/**
 * @return bool True if the $route is current /route or mapped-route -> /route
 */
function is_route(string $route): bool
{
    return Handler::baseRequest() === $route;
}

function is_route_available(string $route): bool
{
    $route_file = $route . '.php';

    return file_exists(PATH_APP_LEGACY_ROUTES . $route_file) or file_exists(PATH_APP_LEGACY_ROUTES_OVERRIDES . $route_file);
}

function is_prevented_route(): bool
{
    return Handler::isPreventedRoute() === true;
}

/**
 * $full=true returns route/and/sub/routes
 * $full=false returns the /route base
 */
function get_route_path(bool $full = false): string
{
    return Handler::getRoutePath($full);
}

/**
 * @return string route name from name.php
 */
function get_route_name(): string
{
    return Handler::getRouteName();
}

function get_template_used(): string
{
    return Handler::getTemplateUsed();
}

/** @deprecated V4 */
function debug(mixed $arguments)
{
    if (empty($arguments)) {
        return;
    }
    if (PHP_SAPI !== 'cli') {
        echo '<pre>';
    }
    foreach (func_get_args() as $value) {
        print_r($value);
    }
    if (PHP_SAPI !== 'cli') {
        echo '</pre>';
    }
}

/** @deprecated V4 */
function check_value(mixed $anything): bool
{
    // @phpstan-ignore-next-line
    if ((!empty($anything) && isset($anything))
        || $anything == '0'
        || (is_countable($anything) && count($anything) > 0)) { // @phpstan-ignore-line
        return true;
    }

    return false;
}

/** @deprecated V4 */
function get_global(mixed $var): mixed
{
    global ${$var};

    return ${$var};
}

function is_apache(): bool
{
    return preg_match('/Apache/i', server()['SERVER_SOFTWARE'] ?? '');
}

function random_values(int $min, int $max, int $limit): array
{
    $min = min($min, $max);
    $max = max($min, $max);
    if ($min == $max) {
        return [$min];
    }
    $minmax_limit = abs($max - $min);
    if ($limit > $minmax_limit) {
        $limit = $minmax_limit;
    }
    $array = [];
    for ($i = 0; $i < $limit; ++$i) {
        $rand = rand($min, $max);
        while (in_array($rand, $array)) {
            $rand = mt_rand($min, $max);
        }
        $array[$i] = $rand;
    }

    return $array;
}

/** @deprecated V4 */
function random_string(int $length): string
{
    switch (true) {
        case function_exists('random_bytes'):
            $r = random_bytes($length);

            break;
        case function_exists('openssl_random_pseudo_bytes'):
            $r = openssl_random_pseudo_bytes($length);

            break;
        case is_readable('/dev/urandom'):
            $r = file_get_contents('/dev/urandom', false, null, 0, $length);

            break;
        default:
            $i = 0;
            $r = '';
            while ($i++ < $length) {
                $r .= chr(mt_rand(0, 255));
            }

            break;
    }

    return substr(bin2hex($r), 0, $length);
}

/** @deprecated V4 */
function timing_safe_compare(?string $safe, ?string $user): bool
{
    $safe ??= '';
    $user ??= '';
    $safe .= chr(0);
    $user .= chr(0);
    $safeLen = strlen($safe);
    $userLen = strlen($user);
    $result = $safeLen - $userLen;
    for ($i = 0; $i < $userLen; ++$i) {
        $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
    }

    return $result === 0;
}

/** @deprecated V4 */
function str_replace_first(string $search, string $replace, string $subject): string
{
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

/** @deprecated V4 */
function str_replace_last(string $search, string $replace, string $subject): string
{
    $pos = strrpos($subject, $search);
    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

/** @deprecated V4 */
function starts_with(string $needle, string $haystack): bool
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/** @deprecated V4 */
function ends_with(string $needle, string $haystack): bool
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return substr($haystack, -$length) === $needle;
}

function array_filter_array(array $array, array $filter_keys, string $get = 'exclusion'): array
{
    $return = [];
    $get = strtolower($get);
    $default_get = 'exclusion';
    foreach ($filter_keys as $k => $v) {
        switch ($get) {
            default:
            case $default_get:
                $get = $default_get;
                if (!array_key_exists($v, $array)) {
                    continue 2;
                }
                $return[$v] = $array[$v];

                break;
            case 'rest':
                unset($array[$v]);

                break;
        }
    }

    return $get == $default_get ? $return : $array;
}

function key_asort(array &$array, string $key): void
{
    $sorter = [];
    $ret = [];
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii] = $array[$ii];
    }
    $array = $ret;
}

function array_utf8encode(array &$arr): array
{
    array_walk_recursive($arr, function (&$val, $key) {
        if (is_int($val)) {
            $val = (string) $val;
        }
        if ($val !== null) {
            $encoding = mb_detect_encoding($val);
            if ($encoding == false) {
                $val = null;
            } else {
                $val = mb_convert_encoding($val, 'UTF-8', $encoding);
            }
        }
    });

    return $arr;
}

function array_remove_empty(array $haystack): array
{
    foreach ($haystack as $key => $value) {
        if (is_array($value)) {
            $haystack[$key] = array_remove_empty($haystack[$key]);
        }
        if (empty($haystack[$key])) {
            unset($haystack[$key]);
        }
    }

    return $haystack;
}

function abbreviate_number(string|int $number): string
{
    // @phpstan-ignore-next-line
    $number = (0 + str_replace(',', '', (string) $number));
    if (!is_numeric($number) or $number == 0) {
        return (string) $number;
    }
    $abbreviations = [
        24 => 'Y',
        21 => 'Z',
        18 => 'E',
        15 => 'P',
        12 => 'T',
        9 => 'G',
        6 => 'M',
        3 => 'K',
        0 => null,
    ];
    foreach ($abbreviations as $exponent => $abbreviation) {
        if ($number >= pow(10, $exponent)) {
            return round(floatval($number / pow(10, $exponent))) . $abbreviation;
        }
    }

    return (string) $number;
}

function nullify_string(mixed &$string)
{
    if (is_string($string) and $string == '') {
        $string = null;
    }
}

function hex_to_rgb(string $hex): array
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }

    return [$r, $g, $b];
}

function rgb_to_hex(array $rgb): string
{
    $hex = '#';
    $hex .= str_pad(dechex($rgb[0]), 2, '0', STR_PAD_LEFT);
    $hex .= str_pad(dechex($rgb[1]), 2, '0', STR_PAD_LEFT);
    $hex .= str_pad(dechex($rgb[2]), 2, '0', STR_PAD_LEFT);

    return $hex;
}

function html_to_bbcode(string $text): string
{
    $htmltags = [
        '/\<b\>(.*?)\<\/b\>/is',
        '/\<i\>(.*?)\<\/i\>/is',
        '/\<u\>(.*?)\<\/u\>/is',
        '/\<ul.*?\>(.*?)\<\/ul\>/is',
        '/\<li\>(.*?)\<\/li\>/is',
        '/\<img(.*?) src=\"(.*?)\" alt=\"(.*?)\" title=\"Smile(y?)\" \/\>/is',
        '/\<img(.*?) src=\"(.*?)\" (.*?)\>/is',
        '/\<img(.*?) src=\"(.*?)\" alt=\":(.*?)\" .*? \/\>/is',
        '/\<div class=\"quotecontent\"\>(.*?)\<\/div\>/is',
        '/\<div class=\"codecontent\"\>(.*?)\<\/div\>/is',
        '/\<div class=\"quotetitle\"\>(.*?)\<\/div\>/is',
        '/\<div class=\"codetitle\"\>(.*?)\<\/div\>/is',
        '/\<cite.*?\>(.*?)\<\/cite\>/is',
        '/\<blockquote.*?\>(.*?)\<\/blockquote\>/is',
        '/\<div\>(.*?)\<\/div\>/is',
        '/\<code\>(.*?)\<\/code\>/is',
        '/\<br(.*?)\>/is',
        '/\<strong\>(.*?)\<\/strong\>/is',
        '/\<em\>(.*?)\<\/em\>/is',
        '/\<a href=\"mailto:(.*?)\"(.*?)\>(.*?)\<\/a\>/is',
        '/\<a .*?href=\"(.*?)\"(.*?)\>http:\/\/(.*?)\<\/a\>/is',
        '/\<a .*?href=\"(.*?)\"(.*?)\>(.*?)\<\/a\>/is',
    ];
    $bbtags = [
        '[b]$1[/b]',
        '[i]$1[/i]',
        '[u]$1[/u]',
        '[list]$1[/list]',
        '[*]$1',
        '$3',
        '[img]$2[/img]',
        ':$3',
        '\[quote\]$1\[/quote\]',
        '\[code\]$1\[/code\]',
        '',
        '',
        '',
        '\[quote\]$1\[/quote\]',
        '$1',
        '\[code\]$1\[/code\]',
        "\n",
        '[b]$1[/b]',
        '[i]$1[/i]',
        '[email=$1]$3[/email]',
        '[url]$1[/url]',
        '[url=$1]$3[/url]',
    ];
    $text = str_replace("\n", ' ', $text);
    $ntext = preg_replace($htmltags, $bbtags, $text);
    $ntext = preg_replace($htmltags, $bbtags, $ntext);
    if (!$ntext) {
        $ntext = str_replace(['<br>', '<br />'], "\n", $text);
        $ntext = str_replace(['<strong>', '</strong>'], ['[b]', '[/b]'], $ntext);
        $ntext = str_replace(['<em>', '</em>'], ['[i]', '[/i]'], $ntext);
    }
    $ntext = strip_tags($ntext);

    return trim(html_entity_decode($ntext, ENT_QUOTES, 'UTF-8'));
}

function linkify(string $text, array $options = []): string
{
    $attr = '';
    if (array_key_exists('attr', $options)) {
        foreach ($options['attr'] as $key => $value) {
            if (true === is_array($value)) {
                $value = array_pop($value);
            }
            $attr .= sprintf(' %s="%s"', $key, $value);
        }
    }
    $options['attr'] = $attr;
    $ignoreTags = ['head', 'link', 'a', 'script', 'style', 'code', 'pre', 'select', 'textarea', 'button'];
    $chunks = preg_split('/(<.+?>)/is', $text, 0, PREG_SPLIT_DELIM_CAPTURE);
    $openTag = null;
    for ($i = 0; $i < count($chunks); ++$i) {
        if ($i % 2 === 0) { // even numbers are text
            // Only process this chunk if there are no unclosed $ignoreTags
            if (null === $openTag) {
                $chunks[$i] = linkify_urls($chunks[$i], $options);
                $chunks[$i] = linkify_emails($chunks[$i], $options);
            }
        } else { // odd numbers are tags
            // Only process this tag if there are no unclosed $ignoreTags
            if (null === $openTag) {
                // Check whether this tag is contained in $ignoreTags and is not self-closing
                if (preg_match('`<(' . implode('|', $ignoreTags) . ').*(?<!/)>$`is', (string) $chunks[$i], $matches)) {
                    $openTag = $matches[1];
                }
            } else {
                // Otherwise, check whether this is the closing tag for $openTag.
                if (preg_match('`</\s*' . $openTag . '>`i', (string) $chunks[$i], $matches)) {
                    $openTag = null;
                }
            }
        }
    }

    return implode($chunks);
}

function linkify_emails(string $text, array $options = ['attr' => '']): string
{
    $pattern = '~(?xi)
            \b
            (?<!=)           # Not part of a query string
            [A-Z0-9._\'%+-]+ # Username
            @                # At
            [A-Z0-9.-]+      # Domain
            \.               # Dot
            [A-Z]{2,4}       # Something
    ~';
    $callback = function ($match) use ($options) {
        if (is_callable($options['callback'] ?? null)) {
            $cb = $options['callback']($match[0], $match[0], $options);
            if (!is_null($cb)) {
                return $cb;
            }
        }

        return '<a href="mailto:' . $match[0] . '"' . $options['attr'] . '>' . $match[0] . '</a>';
    };

    return preg_replace_callback($pattern, $callback, $text);
}

function linkify_urls(string $text, array $options = ['attr' => ''])
{
    $pattern = '~(?xi)
            (?:
            ((ht|f)tps?://)                    # scheme://
            |                                  #   or
            www\d{0,3}\.                       # "www.", "www1.", "www2." ... "www999."
            |                                  #   or
            www\-                              # "www-"
            |                                  #   or
            [a-z0-9.\-]+\.[a-z]{2,4}(?=/)      # looks like domain name followed by a slash
            )
            (?:                                  # Zero or more:
            [^\s()<>]+                         # Run of non-space, non-()<>
            |                                  #   or
            \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
            )*
            (?:                                  # End with:
            \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
            |                                  #   or
            [^\s`!\-()\[\]{};:\'".,<>?«»“”‘’]  # not a space or one of these punct chars
            )
    ~';
    $callback = function ($match) use ($options) {
        $caption = $match[0];
        $pattern = '~^(ht|f)tps?://~';
        if (0 === preg_match($pattern, (string) $match[0])) {
            $match[0] = 'http://' . $match[0];
        }
        if (is_callable($options['callback'] ?? null)) {
            $cb = $options['callback']($match[0], $caption, $options);
            if (!is_null($cb)) {
                return $cb;
            }
        }

        return '<a href="' . $match[0] . '"' . $options['attr'] . '>' . $caption . '</a>';
    };

    return preg_replace_callback($pattern, $callback, $text);
}

function linkify_safe(string $text, array $options = [])
{
    $options = array_merge([
        'attr' => [
            'rel' => 'nofollow',
            'target' => '_blank'
        ]
    ], $options);

    return linkify($text, $options);
}

/** @deprecated V4 */
function errorsAsExceptions(
    int $severity,
    string $message,
    string $file,
    int $line
): void {
    throw new ErrorException($message, 0, $severity, $file, $line);
}

function writeToStderr(string $message): void
{
    fwrite(fopen('php://stderr', 'wb'), $message . "\n");
}

/** @deprecated V4 */
function exception_to_error(Throwable $e, bool $print = true): string
{
    $errorId = random_string(16);
    $isDocker = env()['CHEVERETO_SERVICING'] === 'docker';
    $device = $isDocker ? 'stderr' : 'error_log';
    $debug_level = Config::system()->debugLevel();
    if (!in_array($debug_level, [0, 1, 2, 3])) {
        $debug_level = 1;
    }
    $internal_code = 500;
    $internal_error = '<b>Aw, snap!</b> ' . get_set_status_header_desc($internal_code);
    $table = [
        0 => "debug is disabled",
        1 => "debug @ $device",
        2 => "debug @ print",
        3 => "debug @ print,$device",
    ];
    $internal_error .= ' [' . $table[$debug_level] . '] - https://chv.to/v4debug';
    $message = [$internal_error, '', '** errorId #' . $errorId . ' **'];
    $previous = $e;
    $messageStock = [];
    $i = 0;
    do {
        $code = $previous->getCode();
        $messageStock[$i] = [$previous->getMessage(), safe_html($previous->getMessage())];
        $message[] = '>> ' . get_class($e) . " [$code]: <b>%message_$i%</b>";
        $message[] = 'At ' . absolute_to_relative($previous->getFile()) . ':' . $previous->getLine() . "\n";
        $i++;
    } while ($previous = $previous->getPrevious());
    $stack = '<b>Stack trace:</b>';
    $message[] = $stack;
    $rtn = '';
    $count = 0;
    foreach ($e->getTrace() as $frame) {
        $args = '';
        if (isset($frame['args'])) {
            $args = [];
            foreach ($frame['args'] as $arg) {
                switch (true) {
                    case is_string($arg):
                        if (file_exists($arg)) {
                            $arg = absolute_to_relative($arg);
                        }
                        $args[] = "'" . $arg . "'";

                        break;
                    case is_array($arg):
                        $args[] = 'Array';

                        break;
                    case is_null($arg):
                        $args[] = 'NULL';

                        break;
                    case is_bool($arg):
                        $args[] = ($arg) ? 'true' : 'false';

                        break;
                    case is_object($arg):
                        $args[] = get_class($arg);

                        break;
                    case is_resource($arg):
                        $args[] = get_resource_type($arg);

                        break;
                    default:
                        $args[] = $arg;

                        break;
                }
            }
            $args = join(', ', $args);
        }
        $rtn .= sprintf(
            "#%s %s(%s): %s(%s)\n",
            $count,
            isset($frame['file']) ? absolute_to_relative($frame['file']) : 'unknown file',
            isset($frame['line']) ? $frame['line'] : 'unknown line',
            (isset($frame['class'])) ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'],
            $args
        );
        ++$count;
    }
    $message[] = $rtn;
    $messageEcho = nl2br(implode("\n", $message));
    $messageLog = "\n" . strip_tags(nl2br(implode("\n", $message)));
    foreach ($messageStock as $pos => $safeMessage) {
        $messageEcho = strtr($messageEcho, ["%message_$pos%" => $safeMessage[1]]);
        $messageLog = strtr($messageLog, ["%message_$pos%" => $safeMessage[0]]);
    }
    set_status_header($internal_code);
    if ($print && in_array($debug_level, [2, 3])) {
        echo PHP_SAPI !== 'cli' ? $messageEcho : $messageLog;
    }
    if (in_array($debug_level, [1, 3])) {
        error_log($messageLog);
    }
    if ($isDocker) {
        writeToStderr($messageLog);
    }

    return $errorId;
}

function datetimegmt(?string $format = null): string
{
    return gmdate(!is_null($format) ? $format : 'Y-m-d H:i:s');
}

function datetime(?string $format = null): string
{
    return date(!is_null($format) ? $format : 'Y-m-d H:i:s');
}

function datetime_tz(string $tz, ?string $format = null): string
{
    $date = date_create('now', timezone_open($tz));

    return date_format($date, !is_null($format) ? $format : 'Y-m-d H:i:s');
}

function is_valid_timezone(string $tzid): bool
{
    $valid = [];
    $tza = timezone_abbreviations_list();
    foreach ($tza as $zone) {
        foreach ($zone as $item) {
            $valid[$item['timezone_id']] = true;
        }
    }
    unset($valid['']);

    return $valid[$tzid] ?? false;
}

function datetimegmt_convert_tz(string $datetimegmt, string $tz): string
{
    if (!is_valid_timezone($tz)) {
        return $datetimegmt;
    }
    $date = new \DateTime($datetimegmt . '+00');
    $date->setTimezone(new \DateTimeZone($tz));

    return $date->format('Y-m-d H:i:s');
}

/**
 * Returns the difference between two UTC dates in the given format (default seconds)
 * @return integer `$new (current) - $old`
 */
function datetime_diff(
    string $oldDatetime,
    ?string $newDatetime = null,
    string $format = 's'
): int {
    if (!in_array($format, ['s', 'm', 'h', 'd'])) {
        $format = 's';
    }
    if ($newDatetime == null) {
        $newDatetime = datetimegmt();
    }
    $tz = new \DateTimeZone('UTC');
    $oldDateTime = new \DateTime($oldDatetime, $tz);
    $newDateTime = new \DateTime($newDatetime, $tz);
    $diff = $newDateTime->getTimestamp() - $oldDateTime->getTimestamp(); // In seconds
    $timeconstant = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    return intval($diff / $timeconstant[$format]);
}

function datetime_add(string $datetime, string $add)
{
    return datetime_alter($datetime, $add, 'add');
}

function datetime_sub(string $datetime, string $sub)
{
    return datetime_alter($datetime, $sub, 'sub');
}

function datetime_modify(string $datetime, string $var)
{
    return datetime_alter($datetime, $var, 'modify');
}

function datetime_alter(string $datetime, string $var, $action = 'add'): string
{
    if (!in_array($action, ['add', 'sub', 'modify'])) {
        return $datetime;
    }
    $DateTime = new \DateTime($datetime);
    if ($action == 'modify') {
        $DateTime->$action($var);
    } else {
        try {
            $interval = new DateInterval($var);
        } catch (Throwable $e) {
            return $datetime;
        }
        $DateTime->$action($interval);
    }

    return $DateTime->format('Y-m-d H:i:s');
}

function dateinterval(string $duration): DateInterval|bool
{
    try {
        return new DateInterval($duration);
    } catch (Exception $e) {
    }

    return false;
}

function get_client_ip(): string
{
    $key = env()['CHEVERETO_HEADER_CLIENT_IP'];
    $key = $key === ''
        ? 'REMOTE_ADDR'
        : 'HTTP_' . strtoupper(str_replace('-', '_', $key));

    return server()[$key] ?? '';
}

function get_client_languages(): array
{
    $acceptedLanguages = server()['HTTP_ACCEPT_LANGUAGE'] ?? '';
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})*)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptedLanguages, $lang_parse);
    $langs = $lang_parse[1];
    $ranks = $lang_parse[4];
    $lang2pref = [];
    for ($i = 0; $i < count($langs); ++$i) {
        $lang2pref[$langs[$i]] = (float) (!empty($ranks[$i]) ? $ranks[$i] : 1);
    }
    $cmpLangs = function ($a, $b) use ($lang2pref) {
        if ($lang2pref[$a] > $lang2pref[$b]) {
            return -1;
        } elseif ($lang2pref[$a] < $lang2pref[$b]) {
            return 1;
        } elseif (strlen($a) > strlen($b)) {
            return -1;
        } elseif (strlen($a) < strlen($b)) {
            return 1;
        } else {
            return 0;
        }
    };
    if (is_callable($cmpLangs)) {
        uksort($lang2pref, $cmpLangs);
    }

    return $lang2pref;
}

/**
 * Parses a user agent string into its important parts.
 *
 * @author Jesse G. Donat <donatj@gmail.com>
 *
 * @see https://github.com/donatj/PhpUserAgent
 * @see http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 *
 */
function parse_user_agent(?string $u_agent = null): array
{
    if (is_null($u_agent) && isset(server()['HTTP_USER_AGENT'])) {
        $u_agent = server()['HTTP_USER_AGENT'];
    }
    $platform = null;
    $browser = null;
    $version = null;
    $empty = ['platform' => $platform, 'browser' => $browser, 'version' => $version];

    if (!$u_agent) {
        return $empty;
    }

    if (preg_match('/\((.*?)\)/im', (string) $u_agent, $parent_matches)) {
        preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox)
            (?:\ [^;]*)?
            (?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

        $priority = ['Android', 'Xbox'];
        $result['platform'] = array_unique($result['platform']);
        if (count($result['platform']) > 1) {
            if ($keys = array_intersect($priority, $result['platform'])) {
                $platform = reset($keys);
            } else {
                $platform = $result['platform'][0];
            }
        } elseif (isset($result['platform'][0])) {
            $platform = $result['platform'][0];
        }
    }

    if ($platform == 'linux-gnu') {
        $platform = 'Linux';
    } elseif ($platform == 'CrOS') {
        $platform = 'Chrome OS';
    }

    preg_match_all(
        '%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
            (?:\)?;?)
            (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
        $u_agent,
        $result,
        PREG_PATTERN_ORDER
    );
    if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
        return $empty;
    }
    $browser = $result['browser'][0];
    $version = $result['version'][0];
    $find = function ($search, &$key) use ($result) {
        $xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
        if ($xkey !== false) {
            $key = $xkey;

            return true;
        }

        return false;
    };
    $key = 0;
    if ($browser == 'Iceweasel') {
        $browser = 'Firefox';
    } elseif ($find('Playstation Vita', $key)) {
        $platform = 'PlayStation Vita';
        $browser = 'Browser';
    } elseif ($find('Kindle Fire Build', $key) || $find('Silk', $key)) {
        $browser = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
        $platform = 'Kindle Fire';
        if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
            $version = $result['version'][array_search('Version', $result['browser'])];
        }
    } elseif ($find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS') {
        $browser = 'NintendoBrowser';
        $version = $result['version'][$key];
    } elseif ($find('Kindle', $key)) {
        $browser = $result['browser'][$key];
        $platform = 'Kindle';
        $version = $result['version'][$key];
    } elseif ($find('OPR', $key)) {
        $browser = 'Opera Next';
        $version = $result['version'][$key];
    } elseif ($find('Opera', $key)) {
        $browser = 'Opera';
        $find('Version', $key);
        $version = $result['version'][$key];
    } elseif ($find('Chrome', $key)) {
        $browser = 'Chrome';
        $version = $result['version'][$key];
    } elseif ($find('Midori', $key)) {
        $browser = 'Midori';
        $version = $result['version'][$key];
    } elseif ($browser == 'AppleWebKit') {
        if (($platform == 'Android')) {
            $browser = 'Android Browser';
        } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
            $browser = 'BlackBerry Browser';
        } elseif ($find('Safari', $key)) {
            $browser = 'Safari';
        }

        $find('Version', $key);

        $version = $result['version'][$key];
    } elseif ($browser == 'MSIE' || strpos($browser, 'Trident') !== false) {
        if ($find('IEMobile', $key)) {
            $browser = 'IEMobile';
        } else {
            $browser = 'MSIE';
            $key = 0;
        }
        $version = $result['version'][$key];
    } elseif ($key = preg_grep("/playstation \d/i", array_map('strtolower', $result['browser']))) {
        $key = reset($key);

        $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
        $browser = 'NetFront';
    }

    return ['platform' => $platform, 'browser' => $browser, 'version' => $version];
}

function is_real_email_address(string $email): bool
{
    $valid = true;
    $atIndex = strrpos($email, '@');
    if (is_bool($atIndex) && $atIndex === false) {
        $valid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            $valid = false;
        } elseif ($domainLen < 1 || $domainLen > 255) {
            $valid = false;
        } elseif ($local[0] == '.' || $local[$localLen - 1] == '.') {
            $valid = false;
        } elseif (preg_match('/\\.\\./', $local)) {
            $valid = false;
        } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            $valid = false;
        } elseif (preg_match('/\\.\\./', $domain)) {
            $valid = false;
        } elseif (!preg_match(
            '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
            str_replace('\\\\', '', $local)
        )) {
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace('\\\\', '', $local))) {
                $valid = false;
            }
        }
        if ($valid && !(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
            $valid = false;
        }
    }

    return $valid;
}

function is_valid_hex_color(string $string, bool $prefix = true): bool
{
    return preg_match(
        '/#'
        . ($prefix ? '?' : '')
        . '([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})/',
        $string
    ) === 1;
}

function is_valid_ip(string $ip): bool
{
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

function remove_spaces(string $string): string
{
    return str_replace(' ', '', $string);
}

function sanitize_path_slashes(string $path): string
{
    return preg_replace('#/+#', '/', $path);
}

function sanitize_directory_separator(string $path): string
{
    return preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
}

function sanitize_relative_path(string $path): string
{
    $path = forward_slash($path);
    $path = sanitize_path_slashes($path);
    $path = preg_replace('#(\.+/)+#', '', $path);

    return sanitize_path_slashes($path);
}

function rrmdir(string $dir): void
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . '/' . $object)) {
                    rrmdir($dir . '/' . $object);
                } else {
                    unlinkIfExists($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}

/**
 * This function was stolen from chyrp.net (MIT).
 */
function sanitize_string(
    string $string,
    bool $force_lowercase = true,
    bool $only_alphanumerics = false,
    int $truncate = 100
): string {
    $strip = [
        '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '=', '+', '{',
        '}', '\\', '|', ';', ':', '\'', "'", '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;',
        'â€”', 'â€“', ',', '<', '.', '>', '/', '?',
    ];
    $clean = trim(str_replace($strip, '', strip_tags($string)));
    $clean = preg_replace('/\s+/', '-', $clean);
    $clean = ($only_alphanumerics ? preg_replace('/[^a-zA-Z0-9]/', '', $clean) : $clean);
    $clean = ($truncate ? substr($clean, 0, $truncate) : $clean);

    return $force_lowercase
        ? (
            function_exists('mb_strtolower')
            ?
                mb_strtolower($clean, 'UTF-8')
                : strtolower($clean)
        )
        : $clean;
}

/**
 * Original PHP code by Chirp Internet: www.chirp.com.au */
function truncate(
    string $string,
    int $limit,
    ?string $break = null,
    string $pad = '...'
): string {
    $encoding = 'UTF-8';
    if (mb_strlen($string, $encoding) <= $limit) {
        return $string;
    }
    if (is_null($break) or $break == '') {
        $string = trim(mb_substr($string, 0, $limit - strlen($pad), $encoding)) . $pad;
    } else {
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < mb_strlen($string, $encoding) - 1) {
                $string = mb_substr($string, 0, $breakpoint, $encoding) . $pad;
            }
        }
    }

    return $string;
}

function unaccent_string(string $string): string
{
    if (function_exists('mb_detect_encoding')) {
        $utf8 = strtolower(mb_detect_encoding($string)) == 'utf-8';
    } else {
        $length = strlen($string);
        $utf8 = true;
        for ($i = 0; $i < $length; ++$i) {
            $c = ord($string[$i]);
            if ($c < 0x80) {
                $n = 0;
            } // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) {
                $n = 1;
            } // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) {
                $n = 2;
            } // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) {
                $n = 3;
            } // 11110bbb
            elseif (($c & 0xFC) == 0xF8) {
                $n = 4;
            } // 111110bb
            elseif (($c & 0xFE) == 0xFC) {
                $n = 5;
            } // 1111110b
            else {
                return '';
            } // Does not match any model
            for ($j = 0; $j < $n; ++$j) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length)
                    || ((ord($string[$i]) & 0xC0) != 0x80)
                ) {
                    $utf8 = false;

                    break;
                }
            }
        }
    }
    if (!$utf8) {
        $string = mb_convert_encoding($string, 'UTF-8');
    }
    $transliteration = [
        'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
        'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
        'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
        'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
        'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
        'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
        'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
        'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
        'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
        'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
        'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
        'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
        'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
        'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
        'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
        'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
        'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
        'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
        'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
        'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
        'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
        'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
        'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
        'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
        'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
        'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
        'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
        'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
        'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
        'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
        'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
        'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
        'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
        'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
        'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
        'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
        'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
        'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
        'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
        'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
        'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
        'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
        'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
        'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
        'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
        'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
        'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
        'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
        'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
        'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
        'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
        'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
        'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
        'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
        'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
        'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
        'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
        'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
        'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
        'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
        'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
        'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
        'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
        'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
        'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
        'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
        'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
        'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
        'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
        'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
        'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
        'Ж' => 'Z', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
        'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
        'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'K', 'Ц' => 'T', 'Ч' => 'C',
        'Ш' => 'S', 'Щ' => 'S', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Y', 'Я' => 'Y',
        'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E',
        'ё' => 'E', 'ж' => 'Z', 'з' => 'Z', 'и' => 'I', 'й' => 'I', 'к' => 'K',
        'л' => 'L', 'м' => 'M', 'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R',
        'с' => 'S', 'т' => 'T', 'у' => 'U', 'ф' => 'F', 'х' => 'K', 'ц' => 'T',
        'ч' => 'C', 'ш' => 'S', 'щ' => 'S', 'ы' => 'Y', 'э' => 'E', 'ю' => 'Y',
        'я' => 'Y', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
        'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
        'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
        'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
        'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
        'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
        'ჯ' => 'j', 'ჰ' => 'h', 'ḩ' => 'h', 'ừ' => 'u', 'ế' => 'e', 'ả' => 'a',
        'ị' => 'i', 'ậ' => 'a', 'ệ' => 'e', 'ỉ' => 'i', 'ộ' => 'o', 'ồ' => 'o',
        'ề' => 'e', 'ơ' => 'o', 'ạ' => 'a', 'ẵ' => 'a', 'ư' => 'u', 'ắ' => 'a',
        'ằ' => 'a', 'ầ' => 'a', 'ḑ' => 'd', 'Ḩ' => 'H', 'Ḑ' => 'D',
        'ş' => 's', 'ţ' => 't', 'ễ' => 'e',
    ];
    $string = str_replace(array_keys($transliteration), array_values($transliteration), $string);
    if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
        $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
    }

    return $string;
}

function safe_html(mixed $var, int $flag = ENT_QUOTES | ENT_HTML5, array $skip = []): string|array|null
{
    if (!is_array($var)) {
        return $var === null
            ? null
            : htmlspecialchars((string) $var, $flag, 'UTF-8', false);
    }
    $safe_array = [];
    foreach ($var as $k => $v) {
        if (in_array($k, $skip, true)) {
            $safe_array[$k] = $v;

            continue;
        }
        $safe_array[$k] = is_array($v)
            ? safe_html($v, $flag, $skip)
            : (
                $v === null
                    ? null
                    : safe_html($v, $flag, $skip)
            );
    }

    return $safe_array;
}

function format_bytes(mixed $bytes, int $round = 1): string
{
    if (!is_numeric($bytes)) {
        return '';
    }
    if ($bytes < 1000) {
        return "$bytes B";
    }
    $units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    foreach ($units as $k => $v) {
        $multiplier = pow(1000, $k + 1);
        $threshold = $multiplier * 1000;
        if ($bytes < $threshold) {
            $size = round($bytes / $multiplier, $round);

            return "$size $v";
        }
    }

    return '';
}

function get_bytes(string $size, ?int $cut = null): int
{
    if ($cut == null) {
        $suffix = substr($size, -3);
        $suffix = preg_match('/([A-Za-z]){3}/', $suffix) ? $suffix : substr($size, -2);
    } else {
        $suffix = substr($size, $cut);
    }
    $number = (int) str_replace($suffix, '', $size);
    $suffix = strtoupper($suffix);

    $units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']; // Default dec units

    if (strlen($suffix) == 3) { // Convert units to bin
        foreach ($units as &$unit) {
            $split = str_split($unit);
            $unit = $split[0] . 'I' . $split[1];
        }
    }

    if (strlen($suffix) == 1) {
        $suffix .= 'B'; // Adds missing "B" for shorthand ini notation (Turns 1G into 1GB)
    }
    if (!in_array($suffix, $units)) {
        return $number;
    }
    $pow_factor = array_search($suffix, $units) + 1;

    return $number * pow(strlen($suffix) == 2 ? 1000 : 1024, $pow_factor);
}

function bytes_to_mb(int $bytes): float
{
    return round($bytes / pow(10, 6));
}

function get_ini_bytes(string $size): int
{
    return get_bytes($size, -1);
}

function add_trailing_slashes(string $string): string
{
    return add_ending_slash(add_starting_slash($string));
}

function add_starting_slash(string $string): string
{
    return '/' . ltrim($string, '/');
}

function add_ending_slash(string $string): string
{
    return rtrim($string, '/') . '/';
}

function filter_string_polyfill(string $string): string
{
    $str = preg_replace('/\x00|<[^>]*>?/', '', $string);

    return str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
}

function seoUrlfy(string $text): string
{
    $prepare = $text;
    $prepare = preg_replace('/[\\\\\/\~\&\!\'\"\?]+/', '', $prepare);
    $prepare = preg_replace('/[\s-]+/', ' ', $prepare);
    $prepare = str_replace(' ', '-', trim($prepare));
    $prepare = strip_tags($prepare);

    return urlencode($prepare);
}

function forward_slash(string $string): string
{
    return str_replace('\\', '/', $string);
}

function relative_to_absolute(string $filepath): string
{
    return str_replace(Config::host()->hostnamePath(), PATH_PUBLIC, forward_slash($filepath));
}

function relative_to_url(string $filepath, ?string $root_url = null): string
{
    if (!check_value($root_url)) {
        $root_url = URL_APP_PUBLIC;
    }

    return str_replace(Config::host()->hostnamePath(), $root_url, forward_slash($filepath));
}

function url_to_relative(string $url, ?string $root_url = null): string
{
    if (!check_value($root_url)) {
        $root_url = URL_APP_PUBLIC;
    }

    return str_replace_first($root_url, Config::host()->hostnamePath(), $url);
}

function absolute_to_relative(string $filepath): string
{
    return str_replace_first(PATH_PUBLIC, Config::host()->hostnamePath(), forward_slash($filepath));
}

function absolute_to_url(string $filepath, ?string $root_url = null)
{
    if (!check_value($root_url)) {
        $root_url = URL_APP_PUBLIC;
    }
    if (PATH_PUBLIC === Config::host()->hostnamePath()) {
        return $root_url . ltrim($filepath, '/');
    }

    return str_replace_first(PATH_PUBLIC, $root_url, forward_slash($filepath));
}

function url_to_absolute(string $url, ?string $root_url = null)
{
    if (!check_value($root_url)) {
        $root_url = URL_APP_PUBLIC;
    }

    return str_replace($root_url, PATH_PUBLIC, $url);
}

function get_app_version(bool $full = true): string
{
    if ($full) {
        return APP_VERSION;
    } else {
        preg_match('/\d\.\d/', APP_VERSION, $return);

        return $return[0];
    }
}

/**
 * @deprecated
 */
function get_app_setting(string $key): mixed
{
    $settingsToEnv = [
        'asset_storage_account_id' => 'CHEVERETO_ASSET_STORAGE_ACCOUNT_ID',
        'asset_storage_account_name' => 'CHEVERETO_ASSET_STORAGE_ACCOUNT_NAME',
        'asset_storage_bucket' => 'CHEVERETO_ASSET_STORAGE_BUCKET',
        'asset_storage_key' => 'CHEVERETO_ASSET_STORAGE_KEY',
        'asset_storage_name' => 'CHEVERETO_ASSET_STORAGE_NAME',
        'asset_storage_region' => 'CHEVERETO_ASSET_STORAGE_REGION',
        'asset_storage_secret' => 'CHEVERETO_ASSET_STORAGE_SECRET',
        'asset_storage_server' => 'CHEVERETO_ASSET_STORAGE_SERVER',
        'asset_storage_service' => 'CHEVERETO_ASSET_STORAGE_SERVICE',
        'asset_storage_type' => 'CHEVERETO_ASSET_STORAGE_TYPE',
        'asset_storage_url' => 'CHEVERETO_ASSET_STORAGE_URL',
        'db_driver' => 'CHEVERETO_DB_DRIVER',
        'db_host' => 'CHEVERETO_DB_HOST',
        'db_name' => 'CHEVERETO_DB_NAME',
        'db_pass' => 'CHEVERETO_DB_PASS',
        'db_pdo_attrs' => 'CHEVERETO_DB_PDO_ATTRS',
        'db_port' => 'CHEVERETO_DB_PORT',
        'db_table_prefix' => 'CHEVERETO_DB_TABLE_PREFIX',
        'db_user' => 'CHEVERETO_DB_USER',
        'debug_level' => 'CHEVERETO_DEBUG_LEVEL',
        'disable_php_pages' => 'CHEVERETO_DISABLE_PHP_PAGES',
        'disable_update_http' => 'CHEVERETO_DISABLE_UPDATE_HTTP',
        'disable_update_cli' => 'CHEVERETO_DISABLE_UPDATE_CLI',
        'error_log' => 'CHEVERETO_ERROR_LOG',
        'hostname_path' => 'CHEVERETO_HOSTNAME_PATH',
        'hostname' => 'CHEVERETO_HOSTNAME',
        'https' => 'CHEVERETO_HTTPS',
        'image_formats_available' => 'CHEVERETO_IMAGE_FORMATS_AVAILABLE',
        'image_library' => 'CHEVERETO_IMAGE_LIBRARY',
        'session_save_handler' => 'CHEVERETO_SESSION_SAVE_HANDLER',
        'session_save_path' => 'CHEVERETO_SESSION_SAVE_PATH',
    ];
    $settingEnv = $settingsToEnv[$key] ?? null;
    $env = null;
    if (isset($settingEnv) && array_key_exists($settingEnv, $_ENV)) {
        $env = getenv($settingEnv);
        if ($env === false) {
            $env = null;
        } else {
            switch ($key) {
                case 'https':
                case 'disable_php_pages':
                case 'disable_update_http':
                case 'disable_update_cli':
                    return boolval($env);

                case 'image_formats_available':
                    return explode(',', $env);
            }
        }
    }

    return $env ?? get_global('settings')[$key] ?? null;
}

function get_public_url(string $path = ''): string
{
    return get_base_url($path, true);
}

function get_base_url(string $path = '', bool $public = false): string
{
    $path = sanitize_relative_path($path);

    $base = Config::host()->hostnamePath();
    if ($public) {
        $base = URL_APP_PUBLIC;
    }

    return $base . ltrim($path, '/');
}

function get_current_url(bool $safe = true, array $removeQs = [], bool $protocol = false)
{
    $request_uri = server()['REQUEST_URI'] ?? '';
    $request_path = rtrim(strtok($request_uri, '?') ?: '', '/');
    if ((server()['QUERY_STRING'] ?? false) && $removeQs !== []) {
        parse_str(server()['QUERY_STRING'], $parse);
        foreach ($removeQs as $v) {
            unset($parse[$v]);
        }
        $querystring = $parse !== [] ? http_build_query($parse) : '';
        $request_uri = $request_path;
        if ($querystring !== '') {
            $request_uri .= '/?' . $querystring;
        }
    }
    $path = preg_replace('#' . Config::host()->hostnamePath() . '#', '', rtrim($request_uri, '/') . '/', 1);

    return get_base_url(rtrim($path, '/'), $protocol);
}

function hasEnvDbInfo(): bool
{
    $has = true;
    foreach (['HOST', 'PORT', 'NAME', 'USER', 'PASS', 'DRIVER', 'PDO_ATTRS'] as $prop) {
        $value = env()['CHEVERETO_DB_' . $prop] ?? '';
        if ($value === '') {
            $has = false;

            break;
        }
    }

    return $has;
}

function get_regex_match(
    string $regex,
    string $subject,
    string $delimiter = '/',
    ?int $key = null
): mixed {
    $pattern = $delimiter . $regex . $delimiter;
    preg_match($pattern, $subject, $matches);
    if (array_key_exists($key, $matches)) {
        return $matches[$key];
    } else {
        return $matches;
    }
}

/** @deprecated V4 */
function logger(string $message): void
{
    if (PHP_SAPI !== 'cli') {
        return;
    }
    fwrite(fopen('php://stdout', 'r+'), $message);
}

function curlProgress(int $download_size = 0, int $downloaded = 0): void
{
    if ($download_size == 0) {
        return;
    }
    logger(progress_bar($downloaded, $download_size, ' download'));
}

function progress_bar(int $done, int $total, string $info = "", int $width = 50): string
{
    $perc = (int) round(($done * 100) / $total);
    $bar = (int) round(($width * $perc) / 100);

    return sprintf("  %s%%[%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width - $bar), $info);
}

function curlResolveCa(CurlHandle $ch): void
{
    curl_setopt($ch, CURLOPT_CAINFO, CaBundle::getBundledCaBundlePath());
}

/**
 * Fetch the contents from an URL
 * if $file is set the downloaded file will be saved there.
 */
function fetch_url(string $url, string $file = '', array $options = []): string
{
    $showProgress = PHP_SAPI === 'cli' && ($options['progress'] ?? false);
    if ($url === '') {
        throw new Exception('Missing url');
    }
    if (ini_get('allow_url_fopen') !== '1' && !function_exists('curl_init')) {
        throw new Exception("cURL isn't installed and allow_url_fopen is disabled. Can't perform HTTP requests.");
    }
    $fn = (!function_exists('curl_init') ? 'fgc' : 'curl');
    if ($fn == 'curl') {
        $ch = curl_init();
        curlResolveCa($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, PHP_SAPI === 'cli' ? 0 : 120);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        if ($showProgress) {
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'Chevereto\Legacy\G\curlProgress');
            curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
        }
        if (!empty($options)) {
            foreach ($options as $k => $v) {
                if (!is_int($k)) {
                    continue;
                }
                curl_setopt($ch, $k, $v);
            }
        }
        if ($file !== '') {
            $out = fopen($file, 'wb');
            if (!$out) {
                throw new Exception("Can't open file for read and write");
            }
            curl_setopt($ch, CURLOPT_FILE, $out);
            curl_exec($ch);
            fclose($out);
        } else {
            $contents = curl_exec($ch);
        }
        if ($showProgress) {
            logger("\n");
        }
        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            curl_close($ch);

            throw new Exception('Curl error ' . $curl_error);
        }
        if ($file == '') {
            curl_close($ch);

            return $contents;
        }
    } else {
        $context = stream_context_create([
            'http' => ['ignore_errors' => true, 'follow_location' => false],
        ]);
        $contents = file_get_contents($url, false, $context);
        if (!$contents) {
            throw new Exception("Can't fetch target URL (file_get_contents)");
        }
        if ($file !== '') {
            if (file_put_contents($file, $contents) === false) {
                throw new Exception("Can't fetch target URL (file_put_contents)");
            }
        } else {
            return $contents;
        }
    }

    return $contents ?? '';
}

function getUrlHeaders(string $url, array $options = []): array
{
    $ch = curl_init();
    curlResolveCa($ch);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36');
    if (is_array($options)) {
        foreach ($options as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
    }
    $raw = curl_exec($ch);
    if (curl_errno($ch)) {
        $return['error'] = curl_error($ch);
        $return['http_code'] = 500;
    } else {
        $return = curl_getinfo($ch);
        $return['raw'] = $raw;
    }
    curl_close($ch);

    return $return;
}

function get_execution_time(): float
{
    return microtime(true) - TIME_EXECUTION_START;
}

function bcrypt_cost(float $time = 0.2, int $cost = 9): int
{
    do {
        ++$cost;
        $inicio = microtime(true);
        password_hash('test', PASSWORD_BCRYPT, ['cost' => $cost]);
        $fin = microtime(true);
    } while (($fin - $inicio) < $time);

    return $cost;
}

function is_integer(mixed $var, array $range = []): bool
{
    $options = [];
    if (!empty($range) && is_array($range)) {
        foreach (['min', 'max'] as $k) {
            if (!isset($range[$k])) {
                continue;
            }
            if (is_int($range[$k])) {
                $options['options'][$k . '_range'] = $range[$k];
            }
        }
    }

    return filter_var($var, FILTER_VALIDATE_INT, $options) !== false;
}

function is_url_web(string $string)
{
    return is_url($string, ['http', 'https']);
}

function is_url(mixed $string, array $protocols = []): bool
{
    if (!is_string($string)) {
        return false;
    }
    if (strlen($string) !== strlen(mb_convert_encoding($string, 'UTF-8'))) {
        return false;
    }

    $parsed_url = parse_url($string) ?: [];
    if (count($parsed_url) < 2) { // At least scheme and host
        return false;
    }
    $schemes = $protocols !== []
        ? $protocols
        : ['http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'];
    if (!in_array(strtolower($parsed_url['scheme'] ?? ''), $schemes)) { // Must be a valid scheme
        return false;
    }
    if (!array_key_exists('host', $parsed_url)) { // Host must be there
        return false;
    }

    return true;
}

function is_https(string $string): bool
{
    return strpos($string, 'https://') !== false;
}

function is_valid_url(string $string): bool
{
    if (!is_url($string)) {
        return false;
    }
    $url = preg_replace('/^https/', 'http', $string, 1);
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curlResolveCa($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, PHP_SAPI === 'cli' ? 0 : 120);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result !== false;
    } elseif ((bool) ini_get('allow_url_fopen')) {
        $result = file_get_contents($url);

        return $result !== false;
    }

    throw new LogicException('Unable to check if URL is valid');
}

function is_image_url(mixed $string): bool
{
    if (!is_string($string)) {
        return false;
    }

    return preg_match('/(?:ftp|https?):\/\/(\w+:\w+@)?([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(:[0-9]{1,4}){0,1}|(?:[\w\-]+\.)+[a-z]{2,6})(?:\/[^\/#\?]+)+\.(?:jpe?g|gif|png|bmp|webp)/i', $string) === 1;
}

function is_development_env(): bool
{
    return false;
}

function is_windows_os(): bool
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function is_animated_image($filename): bool
{
    switch (get_file_extension($filename)) {
        case 'gif':
            return is_animated_gif($filename);

        case 'png':
            return is_animated_png($filename);

        case 'webp':
            return is_animated_webp($filename);
    }

    return false;
}

function is_animated_gif($filename): bool
{
    $fh = fopen($filename, 'rb');
    if (!$fh) {
        return false;
    }
    $count = 0;
    while (!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100);
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
    }
    fclose($fh);

    return $count > 1;
}

function is_animated_png(string $filename): bool
{
    $img_bytes = file_get_contents($filename);
    if ($img_bytes) {
        if (strpos(
            substr($img_bytes, 0, strpos($img_bytes, 'IDAT')),
            'acTL'
        ) !== false) {
            return true;
        }
    }

    return false;
}

function is_animated_webp(string $filename): bool
{
    $result = false;
    $fh = fopen($filename, "rb");
    fseek($fh, 12);
    if (fread($fh, 4) === 'VP8X') {
        fseek($fh, 20);
        $myByte = fread($fh, 1);
        $result = ((ord($myByte) >> 1) & 1) ? true : false;
    }
    fclose($fh);

    return $result;
}

/** @deprecated V4 */
function is_writable(string $path): bool
{
    if (\is_writable($path)) {
        return true;
    }
    $testFile = sprintf('%s/%s.tmp', $path, uniqid('data_write_test_'));
    $testFile = str_replace('//', '/', $testFile);

    try {
        $handle = fopen($testFile, 'w');
        fclose($handle);
    } catch (Throwable $e) {
        return false;
    }

    return unlinkIfExists($testFile);
}

function get_mimetype(string $file): string
{
    if (function_exists('finfo_open')) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
    } else {
        if (function_exists('mime_content_type')) {
            return mime_content_type($file);
        } else {
            return extension_to_mime(get_file_extension($file));
        }
    }
}

function mime_to_extension(string $mime): string
{
    return [
        'image/x-windows-bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/bmp' => 'bmp',
        'image/gif' => 'gif',
        'image/pjpeg' => 'jpeg',
        'image/jpeg' => 'jpeg',
        'image/x-png' => 'png',
        'image/png' => 'png',
        'image/x-tiff' => 'tiff',
        'image/tiff' => 'tiff',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'image/webp' => 'webp',
    ][$mime] ?? '';
}

function extension_to_mime(string $ext): string
{
    return [
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'ico' => 'image/vnd.microsoft.icon',
        'webp' => 'image/webp',
    ][$ext] ?? '';
}

function get_image_fileinfo(string $file): array
{
    clearstatcache(true, $file);
    $info = getimagesize($file);
    $filesize = filesize($file);
    if (!$info || $filesize === false) {
        return [];
    }
    $mime = strtolower($info['mime']);
    $extension = mime_to_extension($mime);

    return [
        'filename' => basename($file), // image.jpg
        'name' => basename($file, '.' . get_file_extension($file)), // image
        'width' => intval($info[0]),
        'height' => intval($info[1]),
        'ratio' => $info[0] / $info[1],
        'size' => intval($filesize),
        'size_formatted' => format_bytes($filesize),
        'mime' => $mime,
        'extension' => $extension,
        'bits' => $info['bits'] ?? '',
        'channels' => $info['channels'] ?? '',
        'url' => absolute_to_url($file),
        'md5' => md5_file($file),
    ];
}

function get_file_extension(string $file): string
{
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
}

function get_filename(string $file): string
{
    return basename($file);
}

function get_basename_without_extension(string $filename): string
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = basename($filename);

    return str_replace_last(".$extension", '', $filename);
}

function get_pathname_without_extension(string $filename): string
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    return str_replace_last(".$extension", '', $filename);
}

function change_pathname_extension(string $filename, string $extension): string
{
    $chop = get_pathname_without_extension($filename);
    if ($chop == $filename) {
        return $filename;
    }

    return "$chop.$extension";
}

/**
 * @param string $method: original | random | mixed | id
 * @param string $filename: name of the original file.
 * @deprecated V4
 */
function get_filename_by_method(string $method, string $filename): string
{
    $max_length = 200; // Safe limit, ideally this should be 255 - 4
    $extension = get_file_extension($filename);
    $clean_filename = substr($filename, 0, -(strlen($extension) + 1));
    $clean_filename = unaccent_string($clean_filename); // change áéíóú to aeiou
    $clean_filename = preg_replace('/\s+/', '-', $clean_filename); // change all spaces with dash
    $clean_filename = trim($clean_filename, '-'); // get rid of those ugly dashes
    $clean_filename = preg_replace('/[^\.\w\d-]/i', '', $clean_filename); // remove any non alphanumeric, non underscore, non hyphen and non dot
    if (strlen($clean_filename) == 0) {
        $clean_filename = random_string(32);
    }
    $unlimited_filename = $clean_filename; // No max_length limit
    $capped_filename = substr($clean_filename, 0, $max_length); // 1->200

    switch ($method) {
        default:
        case 'original':
            $name = $capped_filename;

            break;
        case 'random':
            $name = random_string(32);

            break;
        case 'mixed':
            $mixed_chars_length = 16;
            $mixed_chars = random_string($mixed_chars_length);
            if (strlen($capped_filename) + $mixed_chars_length > $max_length) {
                // Bring the scissors Morty
                $capped_filename = substr($capped_filename, 0, $max_length - $mixed_chars_length - strlen($capped_filename));
                // Well done Morty you little piece of shit
            }
            $name = $capped_filename . $mixed_chars;

            break;
        case 'id':
            $name = $unlimited_filename;

            break;
    }

    return $name . '.' . $extension; // 200 + 4
}

/** @deprecated V4 */
function name_unique_file(
    string $path,
    string $filename,
    string $method = 'original'
): string {
    $file = $path . get_filename_by_method($method, $filename);
    if ($method == 'id') {
        return $file;
    }
    while (file_exists($file)) {
        if ($method == 'original') {
            $method = 'mixed';
        }
        $file = $path . get_filename_by_method($method, $filename);
    }

    return $file;
}

/** @deprecated V4 */
function imagefilteropacity(GdImage &$img, ?int $opacity): bool
{
    if (!isset($opacity)) {
        return false;
    }
    $opacity /= 100;
    $w = imagesx($img);
    $h = imagesy($img);
    imagealphablending($img, false);
    $minalpha = 127;
    for ($x = 0; $x < $w; ++$x) {
        for ($y = 0; $y < $h; ++$y) {
            $alpha = (imagecolorat($img, $x, $y) >> 24) & 0xFF;
            if ($alpha < $minalpha) {
                $minalpha = $alpha;
            }
        }
    }
    for ($x = 0; $x < $w; ++$x) {
        for ($y = 0; $y < $h; ++$y) {
            $colorxy = imagecolorat($img, $x, $y);
            $alpha = ($colorxy >> 24) & 0xFF;
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $opacity;
            }
            $alphacolorxy = imagecolorallocatealpha($img, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
            if (!imagesetpixel($img, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }

    return true;
}

/** @deprecated V4 */
function image_allocate_transparency(GdImage $image, string $extension): void
{
    if ($extension == 'png') {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    } else {
        imagetruecolortopalette($image, true, 255);
        imagesavealpha($image, false);
    }
}

/** @deprecated V4 */
function image_copy_transparency(GdImage $image_source, GdImage $image_target)
{
    $transparent_index = imagecolortransparent($image_source);
    $palletsize = imagecolorstotal($image_source);
    if ($transparent_index >= 0 and $transparent_index < $palletsize) {
        $transparent_color = imagecolorsforindex($image_source, $transparent_index);
        $transparent_index = imagecolorallocatealpha($image_target, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'], 127);
        imagefill($image_target, 0, 0, $transparent_index);
        imagecolortransparent($image_target, $transparent_index);
    } else {
        $color = imagecolorallocatealpha($image_target, 0, 0, 0, 127);
        imagefill($image_target, 0, 0, $color);
    }
}

/** @deprecated V4 */
function get_mask_bit_shift(int $bits, string $mask)
{
    if ($bits == 16) {
        // 555
        if ($mask == 0x7c00) {
            return 7;
        }
        if ($mask == 0x03e0) {
            return 2;
        }
        // 656
        if ($mask == 0xf800) {
            return 8;
        }
        if ($mask == 0x07e0) {
            return 3;
        }
    } else {
        if ($mask == 0xff000000) {
            return 24;
        }
        if ($mask == 0x00ff0000) {
            return 16;
        }
        if ($mask == 0x0000ff00) {
            return 8;
        }
    }

    return 0;
}

/** @deprecated V4 */
function imagecreatefrombmp(string $file): GdImage|bool
{
    if (!($fh = fopen($file, 'rb'))) {
        trigger_error('imagecreatefrombmp: Can not open ' . $file, E_USER_WARNING);

        return false;
    }
    $meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread($fh, 14));
    if ($meta['type'] != 19778) {
        trigger_error('imagecreatefrombmp: ' . $file . ' is not a bitmap!', E_USER_WARNING);

        return false;
    }
    $meta += unpack('Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread($fh, 40));
    $bytes_read = 40;
    if ($meta['headersize'] > $bytes_read) {
        $meta += unpack('VrMask/VgMask/VbMask', fread($fh, 12));
        $bytes_read += 12;
    } else {
        if ($meta['bits'] == 16) {
            $meta['rMask'] = 0x7c00;
            $meta['gMask'] = 0x03e0;
            $meta['bMask'] = 0x001f;
        } elseif ($meta['bits'] > 16) {
            $meta['rMask'] = 0x00ff0000;
            $meta['gMask'] = 0x0000ff00;
            $meta['bMask'] = 0x000000ff;
        }
    }
    $meta['bytes'] = $meta['bits'] / 8;
    $meta['decal'] = 4 - (4 * (($meta['width'] * $meta['bytes'] / 4) - floor($meta['width'] * $meta['bytes'] / 4)));
    if ($meta['decal'] == 4) {
        $meta['decal'] = 0;
    }
    if ($meta['imagesize'] < 1) {
        $meta['imagesize'] = $meta['filesize'] - $meta['offset'];
        if ($meta['imagesize'] < 1) {
            $meta['imagesize'] = @filesize($file) - $meta['offset'];
            if ($meta['imagesize'] < 1) {
                trigger_error('imagecreatefrombmp: Can not obtain filesize of ' . $file . '!', E_USER_WARNING);

                return false;
            }
        }
    }
    $meta['colors'] = !$meta['colors'] ? pow(2, $meta['bits']) : $meta['colors'];
    $palette = [];
    if ($meta['bits'] < 16) {
        $palette = unpack('l' . $meta['colors'], fread($fh, $meta['colors'] * 4));
        if ($palette[1] < 0) {
            foreach ($palette as $i => $color) {
                $palette[$i] = $color + 16777216;
            }
        }
    }
    if ($meta['headersize'] > $bytes_read) {
        fread($fh, $meta['headersize'] - $bytes_read);
    }
    $im = imagecreatetruecolor($meta['width'], $meta['height']);
    $data = fread($fh, $meta['imagesize']);
    $p = 0;
    $vide = chr(0);
    $y = $meta['height'] - 1;
    $error = 'imagecreatefrombmp: ' . $file . ' has not enough data!';
    while ($y >= 0) {
        $x = 0;
        while ($x < $meta['width']) {
            switch ($meta['bits']) {
                case 32:
                    if (!($part = substr($data, $p, 4))) {
                        trigger_error($error, E_USER_WARNING);

                        return $im;
                    }
                    $color = unpack('V', $part);
                    $color[1] = (($color[1] & $meta['rMask']) >> get_mask_bit_shift(32, $meta['rMask'])) * 65536 + (($color[1] & $meta['gMask']) >> get_mask_bit_shift(32, $meta['gMask'])) * 256 + (($color[1] & $meta['bMask']) >> get_mask_bit_shift(32, $meta['bMask']));

                    break;
                case 24:
                    if (!($part = substr($data, $p, 3))) {
                        trigger_error($error, E_USER_WARNING);

                        return $im;
                    }
                    $color = unpack('V', $part . $vide);
                    $color[1] = (($color[1] & $meta['rMask']) >> get_mask_bit_shift(24, $meta['rMask'])) * 65536 + (($color[1] & $meta['gMask']) >> get_mask_bit_shift(24, $meta['gMask'])) * 256 + (($color[1] & $meta['bMask']) >> get_mask_bit_shift(24, $meta['bMask']));

                    break;
                case 16:
                    if (!($part = substr($data, $p, 2))) {
                        trigger_error($error, E_USER_WARNING);

                        return $im;
                    }
                    $color = unpack('v', $part);
                    $color[1] = (($color[1] & $meta['rMask']) >> get_mask_bit_shift(16, $meta['rMask'])) * 65536 + (($color[1] & $meta['gMask']) >> get_mask_bit_shift(16, $meta['gMask'])) * 256 + (($color[1] & $meta['bMask']) << 3);

                    break;
                case 8:
                    $color = unpack('n', $vide . substr($data, $p, 1));
                    $color[1] = $palette[$color[1] + 1];

                    break;
                case 4:
                    $color = unpack('n', $vide . substr($data, intval(floor($p)), 1));
                    $color[1] = ($p * 2) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
                    $color[1] = $palette[$color[1] + 1];

                    break;
                case 1:
                    $color = unpack('n', $vide . substr($data, intval(floor($p)), 1));
                    switch (($p * 8) % 8) {
                        case 0:
                            $color[1] = $color[1] >> 7;

                            break;
                        case 1:
                            $color[1] = ($color[1] & 0x40) >> 6;

                            break;
                        case 2:
                            $color[1] = ($color[1] & 0x20) >> 5;

                            break;
                        case 3:
                            $color[1] = ($color[1] & 0x10) >> 4;

                            break;
                        case 4:
                            $color[1] = ($color[1] & 0x8) >> 3;

                            break;
                        case 5:
                            $color[1] = ($color[1] & 0x4) >> 2;

                            break;
                        case 6:
                            $color[1] = ($color[1] & 0x2) >> 1;

                            break;
                        case 7:
                            $color[1] = ($color[1] & 0x1);

                            break;
                    }
                    $color[1] = $palette[$color[1] + 1];

                    break;
                default:
                    trigger_error('imagecreatefrombmp: ' . $file . ' has ' . $meta['bits'] . ' bits and this is not supported!', E_USER_WARNING);

                    return false;
            }
            imagesetpixel($im, $x, $y, $color[1]);
            ++$x;
            $p += $meta['bytes'];
        }
        --$y;
        $p += $meta['decal'];
    }
    fclose($fh);

    return $im;
}

function json_prepare(): void
{
    if (is_development_env()) {
        return;
    }
    if (server()['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        json_output(['status_code' => 400]);
    }
}

function json_error(Throwable $e): array
{
    $message = $e->getMessage();
    $code = $e->getCode();

    return [
        'status_code' => 400,
        'error' => [
            'message' => $message,
            'code' => $code,
        ],
    ];
}

function redirect(string $to = '', int $status = 301): void
{
    if (PHP_SAPI === 'cli') {
        echo sprintf("> Redirection to $to (%s)", (string) $status) . "\n";
        if (!defined('PHPUNIT_CHEVERETO_TESTSUITE')) {
            die();
        }
    }
    if (!is_url_web($to)) {
        $to = get_base_url($to);
    }
    $to = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $to);
    if (php_sapi_name() != 'cgi-fcgi') {
        set_status_header($status);
    }
    header("Location: $to");
    if (!defined('PHPUNIT_CHEVERETO_TESTSUITE')) {
        die();
    }
}

function set_status_header(int $code): void
{
    if (headers_sent()) {
        return;
    }
    $desc = get_set_status_header_desc($code);
    if (empty($desc)) {
        return;
    }
    $protocol = server()['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }
    $set_status_header = "$protocol $code $desc";
    header($set_status_header, true, $code);
}

function get_set_status_header_desc(int $code): string
{
    $codes_to_desc = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended',
    ];
    if (!isset($codes_to_desc[$code])) {
        throw new LogicException('Invalid HTTP status code');
    }

    return $codes_to_desc[$code];
}

function clean_header_comment(string $string): string
{
    return trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $string));
}

/**
 * function xml2array.
 *
 * This function is part of the PHP manual.
 *
 * The PHP manual text and comments are covered by the Creative Commons
 * Attribution 3.0 License, copyright (c) the PHP Documentation Group
 *
 * @author  k dot antczak at livedata dot pl
 * @date    2011-04-22 06:08 UTC
 *
 * @see    http://www.php.net/manual/en/ref.simplexml.php#103617
 *
 * @license http://www.php.net/license/index.php#doc-lic
 * @license http://creativecommons.org/licenses/by/3.0/
 * @license CC-BY-3.0 <http://spdx.org/licenses/CC-BY-3.0>
 */
function xml2array(object $xmlObject, array $out = [])
{
    foreach ((array) $xmlObject as $index => $node) {
        $out[$index] = (is_object($node)) ? xml2array($node) : $node;
    }

    return $out;
}

function get_domain(string $domain, bool $debug = false): string
{
    $original = $domain = strtolower($domain);
    if (filter_var($domain, FILTER_VALIDATE_IP)) {
        return $domain;
    }
    $debug ? print('<strong style="color:green">&raquo;</strong> Parsing: ' . $original) : false;
    $arr = array_slice(array_filter(explode('.', $domain, 4), function ($value) {
        return $value !== 'www';
    }), 0); //rebuild array indexes
    if (count($arr) > 2) {
        $count = count($arr);
        $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);
        $debug ? print(" (parts count: {$count})") : false;
        if (count($_sub) === 2) { // two level TLD
            $removed = array_shift($arr);
            if ($count === 4) { // got a subdomain acting as a domain
                $removed = array_shift($arr);
            }
            $debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
        } elseif (count($_sub) === 1) { // one level TLD
            $removed = array_shift($arr); //remove the subdomain
            if (strlen($_sub[0]) === 2 && $count === 3) { // TLD domain must be 2 letters
                array_unshift($arr, $removed);
            } else {
                // non country TLD according to IANA
                $tlds = [
                    'aero',
                    'arpa',
                    'asia',
                    'biz',
                    'cat',
                    'com',
                    'coop',
                    'edu',
                    'gov',
                    'info',
                    'jobs',
                    'mil',
                    'mobi',
                    'museum',
                    'name',
                    'net',
                    'org',
                    'post',
                    'pro',
                    'tel',
                    'travel',
                    'xxx',
                ];
                if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) { //special TLD don't have a country
                    array_shift($arr);
                }
            }
            $debug ? print("<br>\n" . '[*] One level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
        } else { // more than 3 levels, something is wrong
            for ($i = count($_sub); $i > 1; --$i) {
                $removed = array_shift($arr);
            }
            $debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
        }
    } elseif (count($arr) === 2) {
        $arr0 = array_shift($arr);
        if (
            strpos(join('.', $arr), '.') === false
            && in_array($arr[0], ['localhost', 'test', 'invalid']) === false
        ) { // not a reserved domain
            $debug ? print("<br>\n" . 'Seems invalid domain: <strong>' . join('.', $arr) . '</strong> re-adding: <strong>' . $arr0 . '</strong> ') : false;
            // seems invalid domain, restore it
            array_unshift($arr, $arr0);
        }
    }
    $debug ? print("<br>\n" . '<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">' . join('.', $arr) . "</span><br>\n") : false;

    return join('.', $arr);
}

function getQsParams(): array
{
    $a = [];
    foreach (explode("&", server()["QUERY_STRING"]) as $q) {
        $p = explode('=', $q, 2);
        $a[$p[0]] = isset($p[1]) ? $p[1] : '';
    }

    return $a;
}

function unlinkIfExists(string $filename): bool
{
    if (is_dir($filename)) {
        throw new LogicException(sprintf('Filename %s is a dir', $filename));
    }
    if (file_exists($filename)) {
        return unlink($filename);
    }

    return true;
}

function dsq_hmacsha1($data, $key)
{
    $blocksize = 64;
    $hashfunc = 'sha1';
    if (strlen($key) > $blocksize) {
        $key = pack('H*', $hashfunc($key));
    }
    $key = str_pad($key, $blocksize, chr(0x00));
    $ipad = str_repeat(chr(0x36), $blocksize);
    $opad = str_repeat(chr(0x5c), $blocksize);
    $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));

    return bin2hex($hmac);
}
