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

use function Chevere\Filesystem\fileForPath;
use function Chevere\Filesystem\filePhpReturnForPath;
use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use Chevere\VariableSupport\StorableVariable;
use Chevereto\Config\Config;
use function Chevereto\Legacy\G\get_client_languages;
use Chevereto\Legacy\G\Gettext;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\cookieVar;
use DirectoryIterator;
use RegexIterator;
use Throwable;

class L10n
{
    protected static $instance;

    protected static $processed;

    protected const CHV_DEFAULT_LANGUAGE_EXTENSION = 'po';

    public const PATH_CACHE = PATH_APP_CACHE . 'languages/';

    public const PATH_CACHE_OVERRIDES = self::PATH_CACHE . 'overrides/';

    public const LOCALES_AVAILABLE_FILEPATH = self::PATH_CACHE . '_locales.php';

    public const CHV_BASE_LANGUAGE = 'en';

    protected static Gettext $gettext;

    protected static array $translation_table;

    protected static array $available_languages = [];

    protected static array $enabled_languages = [];

    protected static array $disabled_languages = [];

    protected static string $locale = self::CHV_BASE_LANGUAGE;

    protected static string $forced_locale = '';

    protected static array $override = [];

    protected static array $overridePlural = [];

    public static function cacheFilesystemLocales(): array
    {
        $directory = new DirectoryIterator(PATH_APP_LANGUAGES);
        $regex = new RegexIterator($directory, '/^.+\.' . self::CHV_DEFAULT_LANGUAGE_EXTENSION . '$/i', RegexIterator::GET_MATCH);
        $files = [];
        foreach ($regex as $file) {
            $file = $file[0];
            $locale_code = basename($file, '.' . self::CHV_DEFAULT_LANGUAGE_EXTENSION);
            $files[$locale_code] = self::getLocales()[$locale_code];
        }
        $files = array_filter($files);
        ksort($files);
        fileForPath(self::LOCALES_AVAILABLE_FILEPATH)
          ->createIfNotExists();
        filePhpReturnForPath(self::LOCALES_AVAILABLE_FILEPATH)
          ->put(
              new StorableVariable($files)
          );

        return $files;
    }

    public static function getLocalesAvailable(): array
    {
        $file = filePhpReturnForPath(self::LOCALES_AVAILABLE_FILEPATH);
        if (!$file->filePhp()->file()->exists()) {
            return [];
        }

        return $file->get();
    }

    public static function bindEnabled()
    {
        $locales = self::getLocales();
        self::$available_languages = self::getLocalesAvailable();
        self::$enabled_languages = self::$available_languages;
        foreach (getSetting('languages_disable') as $k) {
            $k = str_replace('_', '-', $k);
            self::$disabled_languages[$k] = $locales[$k];
            unset(self::$enabled_languages[$k]);
        }
    }

    public function __construct(
        string $defaultLanguage,
        bool $autoLanguage,
    ) {
        if (self::$available_languages === []) {
            self::bindEnabled();
        }
        if (self::$forced_locale === '') {
            if (array_key_exists($defaultLanguage, self::$available_languages)) {
                $locale = $defaultLanguage;
            } else {
                $locale = self::$locale;
            }
            if ($autoLanguage) {
                foreach (get_client_languages() as $k => $v) {
                    $user_locale = str_replace('_', '-', $k);
                    if (array_key_exists($user_locale, self::$available_languages) && !array_key_exists($user_locale, self::$disabled_languages)) {
                        $locale = $user_locale;

                        break;
                    } else {
                        foreach (self::$available_languages as $k => $v) {
                            if ($v['base'] == substr($user_locale, 0, 2)) {
                                $locale = $k;

                                break;
                            }
                        }
                    }
                    if ($locale) {
                        break;
                    }
                }
            }
        } else {
            $locale = self::$forced_locale;
        }
        if (!defined('CHV_LANGUAGE_CODE')) {
            define('CHV_LANGUAGE_CODE', $locale);
        }
        if (!defined('CHV_LANGUAGE_FILE')) {
            define('CHV_LANGUAGE_FILE', PATH_APP_LANGUAGES . $locale . '.' . self::CHV_DEFAULT_LANGUAGE_EXTENSION);
        }
        self::processTranslation($locale);
        self::$instance = $this;
    }

    public static function hasInstance(): bool
    {
        return isset(self::$instance);
    }

    public static function getInstance(): static
    {
        if (is_null(self::$instance)) {
            throw new LogicException(
                message('L10n instance is not set'),
                600
            );
        }

        return self::$instance;
    }

    public static function setLocale(string $locale): void
    {
        if (is_null(self::$instance)) {
            self::$forced_locale = $locale;
        } else {
            self::processTranslation($locale);
        }
    }

    public static function processTranslation(string $locale): void
    {
        if ($locale === self::$locale && isset(self::$translation_table)) {
            return;
        }
        if (!array_key_exists($locale, self::$available_languages)
          && array_key_exists(self::$locale, self::$available_languages)) {
            $array = self::$available_languages;
            reset($array);
            $first_key = key($array);
            $locale = $first_key;
        }
        $filename = $locale . '.' . self::CHV_DEFAULT_LANGUAGE_EXTENSION;
        $language_file = PATH_APP_LANGUAGES . $filename;
        $language_override_file = PATH_APP_LANGUAGES . 'overrides/' . $filename;
        self::$locale = $locale;
        $language_handling = [
            'base' => [
                'file' => $language_file,
                'cache_path' => self::PATH_CACHE,
                'table' => [],
            ],
            'override' => [
                'file' => $language_override_file,
                'cache_path' => self::PATH_CACHE . 'overrides/',
                'table' => [],
            ]
        ];
        foreach ($language_handling as $k => $v) {
            $cache_path = $v['cache_path'];
            $cache_file = basename($v['file']) . '.cache.php';
            if (!file_exists($v['file'])) {
                continue;
            }
            if (!file_exists($cache_path)) {
                try {
                    mkdir($cache_path);
                } catch (Throwable $e) {
                    $cache_path = dirname($cache_path);
                }
            }
            self::$gettext = new Gettext([
                'file' => $v['file'],
                'cache_filepath' => $cache_path . $cache_file,
                'cache_header' => $k == 'base',
            ]);
            if ($k == 'base') {
                $translation_plural = self::$gettext->translation_plural;
                $translation_header = self::$gettext->translation_header;
            }
            $language_handling[$k]['table'] = self::$gettext->translation_table;
        }
        if (!isset($translation_plural, $translation_header)) {
            throw new LogicException();
        }
        self::$gettext->translation_plural = $translation_plural;
        self::$gettext->translation_header = $translation_header;
        self::$gettext->translation_table = array_merge(
            $language_handling['base']['table'],
            $language_handling['override']['table']
        );
        self::$translation_table = self::$gettext->translation_table;
    }

    public static function setOverride(string $key, string $msg): void
    {
        self::$override[$key] = $msg;
        self::$override[mb_strtolower($key)] = mb_strtolower($msg);
    }

    public static function setPluralOverride(
        string $key,
        string $msg,
        string $msg_plural,
    ): void {
        self::$overridePlural[$key] = [$msg, $msg_plural];
        self::$overridePlural[mb_strtolower($key)] = [mb_strtolower($msg), mb_strtolower($msg_plural)];
    }

    public static function gettext(string $msg): string
    {
        return self::$override[$msg]
          ?? self::getGettext()->gettext($msg)
          ?? $msg;
    }

    public static function ngettext(string $msg, string $msg_plural, int $count): string
    {
        $overrideMsg = self::$overridePlural[$msg] ?? null;
        if ($overrideMsg !== null) {
            $msg = $overrideMsg[0];
            $msg_plural = $overrideMsg[1];
            $translated = $count == 1 ? $msg : $msg_plural;
            $index_id = self::getGettext()->getPluralKey($count);

            return $overrideMsg[$index_id] ?? $translated;
        }

        return self::getGettext()->ngettext($msg, $msg_plural, $count)
          ?? $msg;
    }

    public static function setStatic(string $var, mixed $value): void
    {
        $instance = self::getInstance();
        $instance::${$var} = $value;
    }

    public static function getStatic(string $var): mixed
    {
        $instance = self::getInstance();

        return $instance::${$var};
    }

    public static function getAvailableLanguages(): array
    {
        return self::getStatic('available_languages');
    }

    public static function getEnabledLanguages(): array
    {
        if (is_null(self::$instance)) {
            self::bindEnabled();
        }

        return self::$enabled_languages;
    }

    public static function getDisabledLanguages(): array
    {
        return self::getStatic('disabled_languages');
    }

    public static function getGettext(): Gettext
    {
        return self::getStatic('gettext');
    }

    public static function getTranslation(): array
    {
        return self::getStatic('translation_table');
    }

    public static function getLocale(): string
    {
        return self::getStatic('locale');
    }

    public static function setCookieLang(string $lang): void
    {
        $args = [
            'USER_SELECTED_LANG',
            $lang,
            time() + (60 * 60 * 24 * 30),
            Config::host()->hostnamePath(),
            Config::host()->hostname(),
            HTTP_APP_PROTOCOL == 'https', // secure,
            true, // httpOnly
        ];
        if (setcookie(...$args)) {
            cookieVar()->put('USER_SELECTED_LANG', $lang);
        }
    }

    public static function getLocales(): array
    {
        return [
      'af' => [
        'code' => 'af',
        'dir' => 'ltr',
        'name' => 'Afrikaans',
        'base' => 'af',
        'short_name' => 'AF',
      ],
      'af-AF' => [
        'code' => 'af-AF',
        'dir' => 'ltr',
        'name' => 'Afrikaans',
        'base' => 'af',
        'short_name' => 'AF (AF)',
      ],
      'am' => [
        'code' => 'am',
        'dir' => 'ltr',
        'name' => '??mari??????',
        'base' => 'am',
        'short_name' => 'AM',
      ],
      'am-AM' => [
        'code' => 'am-AM',
        'dir' => 'ltr',
        'name' => '??mari??????',
        'base' => 'am',
        'short_name' => 'AM (AM)',
      ],
      'an' => [
        'code' => 'an',
        'dir' => 'ltr',
        'name' => 'Aragon??s',
        'base' => 'an',
        'short_name' => 'AN',
      ],
      'an-AN' => [
        'code' => 'an-AN',
        'dir' => 'ltr',
        'name' => 'Aragon??s',
        'base' => 'an',
        'short_name' => 'AN (AN)',
      ],
      'ar' => [
        'code' => 'ar',
        'dir' => 'rtl',
        'name' => '??????????????',
        'base' => 'ar',
        'short_name' => 'AR',
      ],
      'ar-AE' => [
        'code' => 'ar-AE',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????????)',
        'base' => 'ar',
        'short_name' => 'AR (AE)',
      ],
      'ar-BH' => [
        'code' => 'ar-BH',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????????)',
        'base' => 'ar',
        'short_name' => 'AR (BH)',
      ],
      'ar-DZ' => [
        'code' => 'ar-DZ',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????????)',
        'base' => 'ar',
        'short_name' => 'AR (DZ)',
      ],
      'ar-EG' => [
        'code' => 'ar-EG',
        'dir' => 'rtl',
        'name' => '?????????????? (??????)',
        'base' => 'ar',
        'short_name' => 'AR (EG)',
      ],
      'ar-IQ' => [
        'code' => 'ar-IQ',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????)',
        'base' => 'ar',
        'short_name' => 'AR (IQ)',
      ],
      'ar-JO' => [
        'code' => 'ar-JO',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????)',
        'base' => 'ar',
        'short_name' => 'AR (JO)',
      ],
      'ar-KW' => [
        'code' => 'ar-KW',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????)',
        'base' => 'ar',
        'short_name' => 'AR (KW)',
      ],
      'ar-LB' => [
        'code' => 'ar-LB',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????)',
        'base' => 'ar',
        'short_name' => 'AR (LB)',
      ],
      'ar-LY' => [
        'code' => 'ar-LY',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????)',
        'base' => 'ar',
        'short_name' => 'AR (LY)',
      ],
      'ar-MA' => [
        'code' => 'ar-MA',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????)',
        'base' => 'ar',
        'short_name' => 'AR (MA)',
      ],
      'ar-OM' => [
        'code' => 'ar-OM',
        'dir' => 'rtl',
        'name' => '?????????????? (?????????? ????????)',
        'base' => 'ar',
        'short_name' => 'AR (OM)',
      ],
      'ar-QA' => [
        'code' => 'ar-QA',
        'dir' => 'rtl',
        'name' => '?????????????? (??????)',
        'base' => 'ar',
        'short_name' => 'AR (QA)',
      ],
      'ar-SA' => [
        'code' => 'ar-SA',
        'dir' => 'rtl',
        'name' => '?????????????? (????????????????)',
        'base' => 'ar',
        'short_name' => 'AR (SA)',
      ],
      'ar-SD' => [
        'code' => 'ar-SD',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????????)',
        'base' => 'ar',
        'short_name' => 'AR (SD)',
      ],
      'ar-SY' => [
        'code' => 'ar-SY',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????)',
        'base' => 'ar',
        'short_name' => 'AR (SY)',
      ],
      'ar-TN' => [
        'code' => 'ar-TN',
        'dir' => 'rtl',
        'name' => '?????????????? (????????)',
        'base' => 'ar',
        'short_name' => 'AR (TN)',
      ],
      'ar-YE' => [
        'code' => 'ar-YE',
        'dir' => 'rtl',
        'name' => '?????????????? (??????????)',
        'base' => 'ar',
        'short_name' => 'AR (YE)',
      ],
      'as' => [
        'code' => 'as',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'as',
        'short_name' => 'AS',
      ],
      'as-AS' => [
        'code' => 'as-AS',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'as',
        'short_name' => 'AS (AS)',
      ],
      'ast' => [
        'code' => 'ast',
        'dir' => 'ltr',
        'name' => 'Asturianu',
        'base' => 'ast',
        'short_name' => 'AST',
      ],
      'ast-AST' => [
        'code' => 'ast-AST',
        'dir' => 'ltr',
        'name' => 'Asturianu',
        'base' => 'ast',
        'short_name' => 'AST (AST)',
      ],
      'az' => [
        'code' => 'az',
        'dir' => 'ltr',
        'name' => 'Az??rbaycan',
        'base' => 'az',
        'short_name' => 'AZ',
      ],
      'az-AZ' => [
        'code' => 'az-AZ',
        'dir' => 'ltr',
        'name' => 'Az??rbaycan',
        'base' => 'az',
        'short_name' => 'AZ (AZ)',
      ],
      'ba' => [
        'code' => 'ba',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'ba',
        'short_name' => 'BA',
      ],
      'ba-BA' => [
        'code' => 'ba-BA',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'ba',
        'short_name' => 'BA (BA)',
      ],
      'be' => [
        'code' => 'be',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'be',
        'short_name' => 'BE',
      ],
      'be-BY' => [
        'code' => 'be-BY',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'be',
        'short_name' => 'BE (BY)',
      ],
      'bg' => [
        'code' => 'bg',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'bg',
        'short_name' => 'BG',
      ],
      'bg-BG' => [
        'code' => 'bg-BG',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'bg',
        'short_name' => 'BG (BG)',
      ],
      'bn' => [
        'code' => 'bn',
        'dir' => 'ltr',
        'name' => 'Bangla',
        'base' => 'bn',
        'short_name' => 'BN',
      ],
      'bn-BN' => [
        'code' => 'bn-BN',
        'dir' => 'ltr',
        'name' => 'Bangla',
        'base' => 'bn',
        'short_name' => 'BN (BN)',
      ],
      'br' => [
        'code' => 'br',
        'dir' => 'ltr',
        'name' => 'Brezhoneg',
        'base' => 'br',
        'short_name' => 'BR',
      ],
      'br-BR' => [
        'code' => 'br-BR',
        'dir' => 'ltr',
        'name' => 'Brezhoneg',
        'base' => 'br',
        'short_name' => 'BR (BR)',
      ],
      'bs' => [
        'code' => 'bs',
        'dir' => 'ltr',
        'name' => 'Bosanski',
        'base' => 'bs',
        'short_name' => 'BS',
      ],
      'bs-BS' => [
        'code' => 'bs-BS',
        'dir' => 'ltr',
        'name' => 'Bosanski',
        'base' => 'bs',
        'short_name' => 'BS (BS)',
      ],
      'ca' => [
        'code' => 'ca',
        'dir' => 'ltr',
        'name' => '??atal??',
        'base' => 'ca',
        'short_name' => 'CA',
      ],
      'ca-ES' => [
        'code' => 'ca-ES',
        'dir' => 'ltr',
        'name' => '??atal?? (Espanya)',
        'base' => 'ca',
        'short_name' => 'CA (ES)',
      ],
      'ce' => [
        'code' => 'ce',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'ce',
        'short_name' => 'CE',
      ],
      'ce-CE' => [
        'code' => 'ce-CE',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'ce',
        'short_name' => 'CE (CE)',
      ],
      'ch' => [
        'code' => 'ch',
        'dir' => 'ltr',
        'name' => 'Chamoru',
        'base' => 'ch',
        'short_name' => 'CH',
      ],
      'ch-CH' => [
        'code' => 'ch-CH',
        'dir' => 'ltr',
        'name' => 'Chamoru',
        'base' => 'ch',
        'short_name' => 'CH (CH)',
      ],
      'co' => [
        'code' => 'co',
        'dir' => 'ltr',
        'name' => 'Corsu',
        'base' => 'co',
        'short_name' => 'CO',
      ],
      'co-CO' => [
        'code' => 'co-CO',
        'dir' => 'ltr',
        'name' => 'Corsu',
        'base' => 'co',
        'short_name' => 'CO (CO)',
      ],
      'cr' => [
        'code' => 'cr',
        'dir' => 'ltr',
        'name' => 'Cree',
        'base' => 'cr',
        'short_name' => 'CR',
      ],
      'cr-CR' => [
        'code' => 'cr-CR',
        'dir' => 'ltr',
        'name' => 'Cree',
        'base' => 'cr',
        'short_name' => 'CR (CR)',
      ],
      'cs' => [
        'code' => 'cs',
        'dir' => 'ltr',
        'name' => '??e??tina',
        'base' => 'cs',
        'short_name' => 'CS',
      ],
      'cs-CZ' => [
        'code' => 'cs-CZ',
        'dir' => 'ltr',
        'name' => '??e??tina',
        'base' => 'cs',
        'short_name' => 'CS (CZ)',
      ],
      'cv' => [
        'code' => 'cv',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'cv',
        'short_name' => 'CV',
      ],
      'cv-CV' => [
        'code' => 'cv-CV',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'cv',
        'short_name' => 'CV (CV)',
      ],
      'cy' => [
        'code' => 'cy',
        'dir' => 'ltr',
        'name' => 'Cymraeg',
        'base' => 'cy',
        'short_name' => 'CY',
      ],
      'cy-CY' => [
        'code' => 'cy-CY',
        'dir' => 'ltr',
        'name' => 'Cymraeg',
        'base' => 'cy',
        'short_name' => 'CY (CY)',
      ],
      'da' => [
        'code' => 'da',
        'dir' => 'ltr',
        'name' => 'Dansk',
        'base' => 'da',
        'short_name' => 'DA',
      ],
      'da-DK' => [
        'code' => 'da-DK',
        'dir' => 'ltr',
        'name' => 'Dansk',
        'base' => 'da',
        'short_name' => 'DA (DK)',
      ],
      'de' => [
        'code' => 'de',
        'dir' => 'ltr',
        'name' => 'Deutsch',
        'base' => 'de',
        'short_name' => 'DE',
      ],
      'de-AT' => [
        'code' => 'de-AT',
        'dir' => 'ltr',
        'name' => 'Deutsch (??sterreich)',
        'base' => 'de',
        'short_name' => 'DE (AT)',
      ],
      'de-CH' => [
        'code' => 'de-CH',
        'dir' => 'ltr',
        'name' => 'Deutsch (Schweiz)',
        'base' => 'de',
        'short_name' => 'DE (CH)',
      ],
      'de-DE' => [
        'code' => 'de-DE',
        'dir' => 'ltr',
        'name' => 'Deutsch (Deutschland)',
        'base' => 'de',
        'short_name' => 'DE (DE)',
      ],
      'de-LU' => [
        'code' => 'de-LU',
        'dir' => 'ltr',
        'name' => 'Deutsch (Luxemburg)',
        'base' => 'de',
        'short_name' => 'DE (LU)',
      ],
      'el' => [
        'code' => 'el',
        'dir' => 'ltr',
        'name' => '????????????????',
        'base' => 'el',
        'short_name' => 'EL',
      ],
      'el-CY' => [
        'code' => 'el-CY',
        'dir' => 'ltr',
        'name' => '???????????????? (????????????)',
        'base' => 'el',
        'short_name' => 'EL (CY)',
      ],
      'el-GR' => [
        'code' => 'el-GR',
        'dir' => 'ltr',
        'name' => '???????????????? (????????????)',
        'base' => 'el',
        'short_name' => 'EL (GR)',
      ],
      'en' => [
        'code' => 'en',
        'dir' => 'ltr',
        'name' => 'English',
        'base' => 'en',
        'short_name' => 'EN',
      ],
      'en-AU' => [
        'code' => 'en-AU',
        'dir' => 'ltr',
        'name' => 'English (Australia)',
        'base' => 'en',
        'short_name' => 'EN (AU)',
      ],
      'en-CA' => [
        'code' => 'en-CA',
        'dir' => 'ltr',
        'name' => 'English (Canada)',
        'base' => 'en',
        'short_name' => 'EN (CA)',
      ],
      'en-GB' => [
        'code' => 'en-GB',
        'dir' => 'ltr',
        'name' => 'English (UK)',
        'base' => 'en',
        'short_name' => 'EN (GB)',
      ],
      'en-IE' => [
        'code' => 'en-IE',
        'dir' => 'ltr',
        'name' => 'English (Ireland)',
        'base' => 'en',
        'short_name' => 'EN (IE)',
      ],
      'en-IN' => [
        'code' => 'en-IN',
        'dir' => 'ltr',
        'name' => 'English (India)',
        'base' => 'en',
        'short_name' => 'EN (IN)',
      ],
      'en-MT' => [
        'code' => 'en-MT',
        'dir' => 'ltr',
        'name' => 'English (Malta)',
        'base' => 'en',
        'short_name' => 'EN (MT)',
      ],
      'en-NZ' => [
        'code' => 'en-NZ',
        'dir' => 'ltr',
        'name' => 'English (New Zealand)',
        'base' => 'en',
        'short_name' => 'EN (NZ)',
      ],
      'en-PH' => [
        'code' => 'en-PH',
        'dir' => 'ltr',
        'name' => 'English (Philippines)',
        'base' => 'en',
        'short_name' => 'EN (PH)',
      ],
      'en-SG' => [
        'code' => 'en-SG',
        'dir' => 'ltr',
        'name' => 'English (Singapore)',
        'base' => 'en',
        'short_name' => 'EN (SG)',
      ],
      'en-US' => [
        'code' => 'en-US',
        'dir' => 'ltr',
        'name' => 'English (US)',
        'base' => 'en',
        'short_name' => 'EN (US)',
      ],
      'en-ZA' => [
        'code' => 'en-ZA',
        'dir' => 'ltr',
        'name' => 'English (South Africa)',
        'base' => 'en',
        'short_name' => 'EN (ZA)',
      ],
      'eo' => [
        'code' => 'eo',
        'dir' => 'ltr',
        'name' => 'Esperanta',
        'base' => 'eo',
        'short_name' => 'EO',
      ],
      'eo-EO' => [
        'code' => 'eo-EO',
        'dir' => 'ltr',
        'name' => 'Esperanta',
        'base' => 'eo',
        'short_name' => 'EO (EO)',
      ],
      'es' => [
        'code' => 'es',
        'dir' => 'ltr',
        'name' => 'Espa??ol',
        'base' => 'es',
        'short_name' => 'ES',
      ],
      'es-AR' => [
        'code' => 'es-AR',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Argentina)',
        'base' => 'es',
        'short_name' => 'ES (AR)',
      ],
      'es-BO' => [
        'code' => 'es-BO',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Bolivia)',
        'base' => 'es',
        'short_name' => 'ES (BO)',
      ],
      'es-CL' => [
        'code' => 'es-CL',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Chile)',
        'base' => 'es',
        'short_name' => 'ES (CL)',
      ],
      'es-CO' => [
        'code' => 'es-CO',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Colombia)',
        'base' => 'es',
        'short_name' => 'ES (CO)',
      ],
      'es-CR' => [
        'code' => 'es-CR',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Costa Rica)',
        'base' => 'es',
        'short_name' => 'ES (CR)',
      ],
      'es-DO' => [
        'code' => 'es-DO',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Rep??blica Dominicana)',
        'base' => 'es',
        'short_name' => 'ES (DO)',
      ],
      'es-EC' => [
        'code' => 'es-EC',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Ecuador)',
        'base' => 'es',
        'short_name' => 'ES (EC)',
      ],
      'es-ES' => [
        'code' => 'es-ES',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Espa??a)',
        'base' => 'es',
        'short_name' => 'ES (ES)',
      ],
      'es-GT' => [
        'code' => 'es-GT',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Guatemala)',
        'base' => 'es',
        'short_name' => 'ES (GT)',
      ],
      'es-HN' => [
        'code' => 'es-HN',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Honduras)',
        'base' => 'es',
        'short_name' => 'ES (HN)',
      ],
      'es-MX' => [
        'code' => 'es-MX',
        'dir' => 'ltr',
        'name' => 'Espa??ol (M??xico)',
        'base' => 'es',
        'short_name' => 'ES (MX)',
      ],
      'es-NI' => [
        'code' => 'es-NI',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Nicaragua)',
        'base' => 'es',
        'short_name' => 'ES (NI)',
      ],
      'es-PA' => [
        'code' => 'es-PA',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Panam??)',
        'base' => 'es',
        'short_name' => 'ES (PA)',
      ],
      'es-PE' => [
        'code' => 'es-PE',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Per??)',
        'base' => 'es',
        'short_name' => 'ES (PE)',
      ],
      'es-PR' => [
        'code' => 'es-PR',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Puerto Rico)',
        'base' => 'es',
        'short_name' => 'ES (PR)',
      ],
      'es-PY' => [
        'code' => 'es-PY',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Paraguay)',
        'base' => 'es',
        'short_name' => 'ES (PY)',
      ],
      'es-SV' => [
        'code' => 'es-SV',
        'dir' => 'ltr',
        'name' => 'Espa??ol (El Salvador)',
        'base' => 'es',
        'short_name' => 'ES (SV)',
      ],
      'es-US' => [
        'code' => 'es-US',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Estados Unidos)',
        'base' => 'es',
        'short_name' => 'ES (US)',
      ],
      'es-UY' => [
        'code' => 'es-UY',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Uruguay)',
        'base' => 'es',
        'short_name' => 'ES (UY)',
      ],
      'es-VE' => [
        'code' => 'es-VE',
        'dir' => 'ltr',
        'name' => 'Espa??ol (Venezuela)',
        'base' => 'es',
        'short_name' => 'ES (VE)',
      ],
      'et' => [
        'code' => 'et',
        'dir' => 'ltr',
        'name' => 'Eesti',
        'base' => 'et',
        'short_name' => 'ET',
      ],
      'et-EE' => [
        'code' => 'et-EE',
        'dir' => 'ltr',
        'name' => 'Eesti (Eesti)',
        'base' => 'et',
        'short_name' => 'ET (EE)',
      ],
      'eu' => [
        'code' => 'eu',
        'dir' => 'ltr',
        'name' => 'Euskera',
        'base' => 'eu',
        'short_name' => 'EU',
      ],
      'eu-EU' => [
        'code' => 'eu-EU',
        'dir' => 'ltr',
        'name' => 'Euskera',
        'base' => 'eu',
        'short_name' => 'EU (EU)',
      ],
      'fa' => [
        'code' => 'fa',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'fa',
        'short_name' => 'FA',
      ],
      'fa-FA' => [
        'code' => 'fa-FA',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'fa',
        'short_name' => 'FA (FA)',
      ],
      'fi' => [
        'code' => 'fi',
        'dir' => 'ltr',
        'name' => 'Suomi',
        'base' => 'fi',
        'short_name' => 'FI',
      ],
      'fi-FI' => [
        'code' => 'fi-FI',
        'dir' => 'ltr',
        'name' => 'Suomi',
        'base' => 'fi',
        'short_name' => 'FI (FI)',
      ],
      'fj' => [
        'code' => 'fj',
        'dir' => 'ltr',
        'name' => 'Na Vosa Vakaviti',
        'base' => 'fj',
        'short_name' => 'FJ',
      ],
      'fj-FJ' => [
        'code' => 'fj-FJ',
        'dir' => 'ltr',
        'name' => 'Na Vosa Vakaviti',
        'base' => 'fj',
        'short_name' => 'FJ (FJ)',
      ],
      'fo' => [
        'code' => 'fo',
        'dir' => 'ltr',
        'name' => 'F??royskt',
        'base' => 'fo',
        'short_name' => 'FO',
      ],
      'fo-FO' => [
        'code' => 'fo-FO',
        'dir' => 'ltr',
        'name' => 'F??royskt',
        'base' => 'fo',
        'short_name' => 'FO (FO)',
      ],
      'fr' => [
        'code' => 'fr',
        'dir' => 'ltr',
        'name' => 'Fran??ais',
        'base' => 'fr',
        'short_name' => 'FR',
      ],
      'fr-BE' => [
        'code' => 'fr-BE',
        'dir' => 'ltr',
        'name' => 'Fran??ais (Belgique)',
        'base' => 'fr',
        'short_name' => 'FR (BE)',
      ],
      'fr-CA' => [
        'code' => 'fr-CA',
        'dir' => 'ltr',
        'name' => 'Fran??ais (Canada)',
        'base' => 'fr',
        'short_name' => 'FR (CA)',
      ],
      'fr-CH' => [
        'code' => 'fr-CH',
        'dir' => 'ltr',
        'name' => 'Fran??ais (Suisse)',
        'base' => 'fr',
        'short_name' => 'FR (CH)',
      ],
      'fr-FR' => [
        'code' => 'fr-FR',
        'dir' => 'ltr',
        'name' => 'Fran??ais (France)',
        'base' => 'fr',
        'short_name' => 'FR (FR)',
      ],
      'fr-LU' => [
        'code' => 'fr-LU',
        'dir' => 'ltr',
        'name' => 'Fran??ais (Luxembourg)',
        'base' => 'fr',
        'short_name' => 'FR (LU)',
      ],
      'fy' => [
        'code' => 'fy',
        'dir' => 'ltr',
        'name' => 'Frysk',
        'base' => 'fy',
        'short_name' => 'FY',
      ],
      'fy-FY' => [
        'code' => 'fy-FY',
        'dir' => 'ltr',
        'name' => 'Frysk',
        'base' => 'fy',
        'short_name' => 'FY (FY)',
      ],
      'ga' => [
        'code' => 'ga',
        'dir' => 'ltr',
        'name' => 'Gaeilge',
        'base' => 'ga',
        'short_name' => 'GA',
      ],
      'ga-IE' => [
        'code' => 'ga-IE',
        'dir' => 'ltr',
        'name' => 'Gaeilge (??ire)',
        'base' => 'ga',
        'short_name' => 'GA (IE)',
      ],
      'gd' => [
        'code' => 'gd',
        'dir' => 'ltr',
        'name' => 'G??idhlig',
        'base' => 'gd',
        'short_name' => 'GD',
      ],
      'gd-GD' => [
        'code' => 'gd-GD',
        'dir' => 'ltr',
        'name' => 'G??idhlig',
        'base' => 'gd',
        'short_name' => 'GD (GD)',
      ],
      'gl' => [
        'code' => 'gl',
        'dir' => 'ltr',
        'name' => 'Galego',
        'base' => 'gl',
        'short_name' => 'GL',
      ],
      'gl-GL' => [
        'code' => 'gl-GL',
        'dir' => 'ltr',
        'name' => 'Galego',
        'base' => 'gl',
        'short_name' => 'GL (GL)',
      ],
      'gu' => [
        'code' => 'gu',
        'dir' => 'ltr',
        'name' => 'Gujarati',
        'base' => 'gu',
        'short_name' => 'GU',
      ],
      'gu-GU' => [
        'code' => 'gu-GU',
        'dir' => 'ltr',
        'name' => 'Gujarati',
        'base' => 'gu',
        'short_name' => 'GU (GU)',
      ],
      'he' => [
        'code' => 'he',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'he',
        'short_name' => 'HE',
      ],
      'he-IL' => [
        'code' => 'he-IL',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'he',
        'short_name' => 'HE (IL)',
      ],
      'hi' => [
        'code' => 'hi',
        'dir' => 'ltr',
        'name' => '???????????????',
        'base' => 'hi',
        'short_name' => 'HI',
      ],
      'hi-IN' => [
        'code' => 'hi-IN',
        'dir' => 'ltr',
        'name' => '??????????????? (????????????)',
        'base' => 'hi',
        'short_name' => 'HI (IN)',
      ],
      'hr' => [
        'code' => 'hr',
        'dir' => 'ltr',
        'name' => 'Hrvatski',
        'base' => 'hr',
        'short_name' => 'HR',
      ],
      'hr-HR' => [
        'code' => 'hr-HR',
        'dir' => 'ltr',
        'name' => 'Hrvatski',
        'base' => 'hr',
        'short_name' => 'HR (HR)',
      ],
      'hsb' => [
        'code' => 'hsb',
        'dir' => 'ltr',
        'name' => 'Hornjoserb????ina',
        'base' => 'hsb',
        'short_name' => 'HSB',
      ],
      'hsb-HSB' => [
        'code' => 'hsb-HSB',
        'dir' => 'ltr',
        'name' => 'Hornjoserb????ina',
        'base' => 'hsb',
        'short_name' => 'HSB (HSB)',
      ],
      'ht' => [
        'code' => 'ht',
        'dir' => 'ltr',
        'name' => 'Krey??l Ayisyen',
        'base' => 'ht',
        'short_name' => 'HT',
      ],
      'ht-HT' => [
        'code' => 'ht-HT',
        'dir' => 'ltr',
        'name' => 'Krey??l Ayisyen',
        'base' => 'ht',
        'short_name' => 'HT (HT)',
      ],
      'hu' => [
        'code' => 'hu',
        'dir' => 'ltr',
        'name' => 'Magyar',
        'base' => 'hu',
        'short_name' => 'HU',
      ],
      'hu-HU' => [
        'code' => 'hu-HU',
        'dir' => 'ltr',
        'name' => 'Magyar',
        'base' => 'hu',
        'short_name' => 'HU (HU)',
      ],
      'hy' => [
        'code' => 'hy',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'hy',
        'short_name' => 'HY',
      ],
      'hy-HY' => [
        'code' => 'hy-HY',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'hy',
        'short_name' => 'HY (HY)',
      ],
      'ia' => [
        'code' => 'ia',
        'dir' => 'ltr',
        'name' => 'Interlingua',
        'base' => 'ia',
        'short_name' => 'IA',
      ],
      'ia-IA' => [
        'code' => 'ia-IA',
        'dir' => 'ltr',
        'name' => 'Interlingua',
        'base' => 'ia',
        'short_name' => 'IA (IA)',
      ],
      'id' => [
        'code' => 'id',
        'dir' => 'ltr',
        'name' => 'Bahasa Indonesia',
        'base' => 'id',
        'short_name' => 'ID',
      ],
      'id-ID' => [
        'code' => 'id-ID',
        'dir' => 'ltr',
        'name' => 'Bahasa Indonesia',
        'base' => 'id',
        'short_name' => 'ID (ID)',
      ],
      'ie' => [
        'code' => 'ie',
        'dir' => 'ltr',
        'name' => 'Interlingue',
        'base' => 'ie',
        'short_name' => 'IE',
      ],
      'ie-IE' => [
        'code' => 'ie-IE',
        'dir' => 'ltr',
        'name' => 'Interlingue',
        'base' => 'ie',
        'short_name' => 'IE (IE)',
      ],
      'in' => [
        'code' => 'in',
        'dir' => 'ltr',
        'name' => 'Bahasa Indonesia',
        'base' => 'in',
        'short_name' => 'IN',
      ],
      'in-ID' => [
        'code' => 'in-ID',
        'dir' => 'ltr',
        'name' => 'Bahasa Indonesia (Indonesia)',
        'base' => 'in',
        'short_name' => 'IN (ID)',
      ],
      'is' => [
        'code' => 'is',
        'dir' => 'ltr',
        'name' => '??slenska',
        'base' => 'is',
        'short_name' => 'IS',
      ],
      'is-IS' => [
        'code' => 'is-IS',
        'dir' => 'ltr',
        'name' => '??slenska (??sland)',
        'base' => 'is',
        'short_name' => 'IS (IS)',
      ],
      'it' => [
        'code' => 'it',
        'dir' => 'ltr',
        'name' => 'Italiano',
        'base' => 'it',
        'short_name' => 'IT',
      ],
      'it-CH' => [
        'code' => 'it-CH',
        'dir' => 'ltr',
        'name' => 'Italiano (Svizzera)',
        'base' => 'it',
        'short_name' => 'IT (CH)',
      ],
      'it-IT' => [
        'code' => 'it-IT',
        'dir' => 'ltr',
        'name' => 'Italiano (Italia)',
        'base' => 'it',
        'short_name' => 'IT (IT)',
      ],
      'iu' => [
        'code' => 'iu',
        'dir' => 'ltr',
        'name' => 'Inuktitut',
        'base' => 'iu',
        'short_name' => 'IU',
      ],
      'iu-IU' => [
        'code' => 'iu-IU',
        'dir' => 'ltr',
        'name' => 'Inuktitut',
        'base' => 'iu',
        'short_name' => 'IU (IU)',
      ],
      'iw' => [
        'code' => 'iw',
        'dir' => 'ltr',
        'name' => '??????????',
        'base' => 'iw',
        'short_name' => 'IW',
      ],
      'iw-IL' => [
        'code' => 'iw-IL',
        'dir' => 'ltr',
        'name' => '??????????',
        'base' => 'iw',
        'short_name' => 'IW (IL)',
      ],
      'ja' => [
        'code' => 'ja',
        'dir' => 'ltr',
        'name' => '?????????',
        'base' => 'ja',
        'short_name' => 'JA',
      ],
      'ja-JP' => [
        'code' => 'ja-JP',
        'dir' => 'ltr',
        'name' => '?????????',
        'base' => 'ja',
        'short_name' => 'JA (JP)',
      ],
      'ka' => [
        'code' => 'ka',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'ka',
        'short_name' => 'KA',
      ],
      'ka-KA' => [
        'code' => 'ka-KA',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'ka',
        'short_name' => 'KA (KA)',
      ],
      'kk' => [
        'code' => 'kk',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'kk',
        'short_name' => 'KK',
      ],
      'kk-KK' => [
        'code' => 'kk-KK',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'kk',
        'short_name' => 'KK (KK)',
      ],
      'km' => [
        'code' => 'km',
        'dir' => 'ltr',
        'name' => 'Khmer',
        'base' => 'km',
        'short_name' => 'KM',
      ],
      'km-KM' => [
        'code' => 'km-KM',
        'dir' => 'ltr',
        'name' => 'Khmer',
        'base' => 'km',
        'short_name' => 'KM (KM)',
      ],
      'ko' => [
        'code' => 'ko',
        'dir' => 'ltr',
        'name' => '?????????',
        'base' => 'ko',
        'short_name' => 'KO',
      ],
      'ko-KR' => [
        'code' => 'ko-KR',
        'dir' => 'ltr',
        'name' => '?????????',
        'base' => 'ko',
        'short_name' => 'KO (KR)',
      ],
      'ky' => [
        'code' => 'ky',
        'dir' => 'ltr',
        'name' => '????????????????',
        'base' => 'ky',
        'short_name' => 'KY',
      ],
      'ky-KY' => [
        'code' => 'ky-KY',
        'dir' => 'ltr',
        'name' => '????????????????',
        'base' => 'ky',
        'short_name' => 'KY (KY)',
      ],
      'la' => [
        'code' => 'la',
        'dir' => 'ltr',
        'name' => 'Latina',
        'base' => 'la',
        'short_name' => 'LA',
      ],
      'la-LA' => [
        'code' => 'la-LA',
        'dir' => 'ltr',
        'name' => 'Latina',
        'base' => 'la',
        'short_name' => 'LA (LA)',
      ],
      'lb' => [
        'code' => 'lb',
        'dir' => 'ltr',
        'name' => 'L??tzebuergesch',
        'base' => 'lb',
        'short_name' => 'LB',
      ],
      'lb-LB' => [
        'code' => 'lb-LB',
        'dir' => 'ltr',
        'name' => 'L??tzebuergesch',
        'base' => 'lb',
        'short_name' => 'LB (LB)',
      ],
      'lt' => [
        'code' => 'lt',
        'dir' => 'ltr',
        'name' => 'Lietuvi??',
        'base' => 'lt',
        'short_name' => 'LT',
      ],
      'lt-LT' => [
        'code' => 'lt-LT',
        'dir' => 'ltr',
        'name' => 'Lietuvi?? (Lietuva)',
        'base' => 'lt',
        'short_name' => 'LT (LT)',
      ],
      'lv' => [
        'code' => 'lv',
        'dir' => 'ltr',
        'name' => 'Latvie??u',
        'base' => 'lv',
        'short_name' => 'LV',
      ],
      'lv-LV' => [
        'code' => 'lv-LV',
        'dir' => 'ltr',
        'name' => 'Latvie??u (Latvija)',
        'base' => 'lv',
        'short_name' => 'LV (LV)',
      ],
      'mi' => [
        'code' => 'mi',
        'dir' => 'ltr',
        'name' => 'Te Reo M??ori',
        'base' => 'mi',
        'short_name' => 'MI',
      ],
      'mi-MI' => [
        'code' => 'mi-MI',
        'dir' => 'ltr',
        'name' => 'Te Reo M??ori',
        'base' => 'mi',
        'short_name' => 'MI (MI)',
      ],
      'mk' => [
        'code' => 'mk',
        'dir' => 'ltr',
        'name' => '????????????????????',
        'base' => 'mk',
        'short_name' => 'MK',
      ],
      'mk-MK' => [
        'code' => 'mk-MK',
        'dir' => 'ltr',
        'name' => '???????????????????? (????????????????????)',
        'base' => 'mk',
        'short_name' => 'MK (MK)',
      ],
      'ml' => [
        'code' => 'ml',
        'dir' => 'ltr',
        'name' => 'Malayalam',
        'base' => 'ml',
        'short_name' => 'ML',
      ],
      'ml-ML' => [
        'code' => 'ml-ML',
        'dir' => 'ltr',
        'name' => 'Malayalam',
        'base' => 'ml',
        'short_name' => 'ML (ML)',
      ],
      'mo' => [
        'code' => 'mo',
        'dir' => 'ltr',
        'name' => 'Graiul Moldovenesc',
        'base' => 'mo',
        'short_name' => 'MO',
      ],
      'mo-MO' => [
        'code' => 'mo-MO',
        'dir' => 'ltr',
        'name' => 'Graiul Moldovenesc',
        'base' => 'mo',
        'short_name' => 'MO (MO)',
      ],
      'mr' => [
        'code' => 'mr',
        'dir' => 'ltr',
        'name' => '???????????????',
        'base' => 'mr',
        'short_name' => 'MR',
      ],
      'mr-MR' => [
        'code' => 'mr-MR',
        'dir' => 'ltr',
        'name' => '???????????????',
        'base' => 'mr',
        'short_name' => 'MR (MR)',
      ],
      'ms' => [
        'code' => 'ms',
        'dir' => 'ltr',
        'name' => 'Bahasa Melayu',
        'base' => 'ms',
        'short_name' => 'MS',
      ],
      'ms-MY' => [
        'code' => 'ms-MY',
        'dir' => 'ltr',
        'name' => 'Bahasa Melayu',
        'base' => 'ms',
        'short_name' => 'MS (MY)',
      ],
      'mt' => [
        'code' => 'mt',
        'dir' => 'ltr',
        'name' => 'Malti',
        'base' => 'mt',
        'short_name' => 'MT',
      ],
      'mt-MT' => [
        'code' => 'mt-MT',
        'dir' => 'ltr',
        'name' => 'Malti',
        'base' => 'mt',
        'short_name' => 'MT (MT)',
      ],
      'nb' => [
        'code' => 'nb',
        'dir' => 'ltr',
        'name' => '???Norsk Bokm??l???',
        'base' => 'nb',
        'short_name' => 'NB',
      ],
      'nb-NB' => [
        'code' => 'nb-NB',
        'dir' => 'ltr',
        'name' => '???Norsk Bokm??l???',
        'base' => 'nb',
        'short_name' => 'NB (NB)',
      ],
      'ne' => [
        'code' => 'ne',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'ne',
        'short_name' => 'NE',
      ],
      'ne-NE' => [
        'code' => 'ne-NE',
        'dir' => 'ltr',
        'name' => '??????????????????',
        'base' => 'ne',
        'short_name' => 'NE (NE)',
      ],
      'ng' => [
        'code' => 'ng',
        'dir' => 'ltr',
        'name' => 'Oshiwambo',
        'base' => 'ng',
        'short_name' => 'NG',
      ],
      'ng-NG' => [
        'code' => 'ng-NG',
        'dir' => 'ltr',
        'name' => 'Oshiwambo',
        'base' => 'ng',
        'short_name' => 'NG (NG)',
      ],
      'nl' => [
        'code' => 'nl',
        'dir' => 'ltr',
        'name' => 'Nederlands',
        'base' => 'nl',
        'short_name' => 'NL',
      ],
      'nl-BE' => [
        'code' => 'nl-BE',
        'dir' => 'ltr',
        'name' => 'Nederlands (Belgi??)',
        'base' => 'nl',
        'short_name' => 'NL (BE)',
      ],
      'nl-NL' => [
        'code' => 'nl-NL',
        'dir' => 'ltr',
        'name' => 'Nederlands (Nederland)',
        'base' => 'nl',
        'short_name' => 'NL (NL)',
      ],
      'nn' => [
        'code' => 'nn',
        'dir' => 'ltr',
        'name' => 'Norsk',
        'base' => 'nn',
        'short_name' => 'NN',
      ],
      'nn-NN' => [
        'code' => 'nn-NN',
        'dir' => 'ltr',
        'name' => 'Norsk (Nynorsk)',
        'base' => 'nn',
        'short_name' => 'NN (NN)',
      ],
      'no' => [
        'code' => 'no',
        'dir' => 'ltr',
        'name' => 'Norsk',
        'base' => 'no',
        'short_name' => 'NO',
      ],
      'no-NO' => [
        'code' => 'no-NO',
        'dir' => 'ltr',
        'name' => 'Norsk (Norge)',
        'base' => 'no',
        'short_name' => 'NO (NO)',
      ],
      'nv' => [
        'code' => 'nv',
        'dir' => 'ltr',
        'name' => 'Din?? Bizaad',
        'base' => 'nv',
        'short_name' => 'NV',
      ],
      'nv-NV' => [
        'code' => 'nv-NV',
        'dir' => 'ltr',
        'name' => 'Din?? Bizaad',
        'base' => 'nv',
        'short_name' => 'NV (NV)',
      ],
      'oc' => [
        'code' => 'oc',
        'dir' => 'ltr',
        'name' => 'Lenga d?????c',
        'base' => 'oc',
        'short_name' => 'OC',
      ],
      'oc-OC' => [
        'code' => 'oc-OC',
        'dir' => 'ltr',
        'name' => 'Lenga d?????c',
        'base' => 'oc',
        'short_name' => 'OC (OC)',
      ],
      'om' => [
        'code' => 'om',
        'dir' => 'ltr',
        'name' => 'Afaan Oromoo',
        'base' => 'om',
        'short_name' => 'OM',
      ],
      'om-OM' => [
        'code' => 'om-OM',
        'dir' => 'ltr',
        'name' => 'Afaan Oromoo',
        'base' => 'om',
        'short_name' => 'OM (OM)',
      ],
      'pa' => [
        'code' => 'pa',
        'dir' => 'rtl',
        'name' => '???????????? ?????????????????????',
        'base' => 'pa',
        'short_name' => 'PA',
      ],
      'pa-IN' => [
        'code' => 'pa-IN',
        'dir' => 'rtl',
        'name' => '???????????? ????????????????????? (??????????????????)',
        'base' => 'pa',
        'short_name' => 'PA (IN)',
      ],
      'pa-PK' => [
        'code' => 'pa-PK',
        'dir' => 'rtl',
        'name' => '???????????? (??????????)',
        'base' => 'pa',
        'short_name' => 'PA (PK)',
      ],
      'pl' => [
        'code' => 'pl',
        'dir' => 'ltr',
        'name' => 'Polski',
        'base' => 'pl',
        'short_name' => 'PL',
      ],
      'pl-PL' => [
        'code' => 'pl-PL',
        'dir' => 'ltr',
        'name' => 'Polski (Polska)',
        'base' => 'pl',
        'short_name' => 'PL (PL)',
      ],
      'pt' => [
        'code' => 'pt',
        'dir' => 'ltr',
        'name' => 'Portugu??s',
        'base' => 'pt',
        'short_name' => 'PT',
      ],
      'pt-BR' => [
        'code' => 'pt-BR',
        'dir' => 'ltr',
        'name' => 'Portugu??s (Brasil)',
        'base' => 'pt',
        'short_name' => 'PT (BR)',
      ],
      'pt-PT' => [
        'code' => 'pt-PT',
        'dir' => 'ltr',
        'name' => 'Portugu??s (Portugal)',
        'base' => 'pt',
        'short_name' => 'PT (PT)',
      ],
      'qu' => [
        'code' => 'qu',
        'dir' => 'ltr',
        'name' => 'Runa Simi',
        'base' => 'qu',
        'short_name' => 'QU',
      ],
      'qu-QU' => [
        'code' => 'qu-QU',
        'dir' => 'ltr',
        'name' => 'Runa Simi',
        'base' => 'qu',
        'short_name' => 'QU (QU)',
      ],
      'rm' => [
        'code' => 'rm',
        'dir' => 'ltr',
        'name' => 'Rumantsch',
        'base' => 'rm',
        'short_name' => 'RM',
      ],
      'rm-RM' => [
        'code' => 'rm-RM',
        'dir' => 'ltr',
        'name' => 'Rumantsch',
        'base' => 'rm',
        'short_name' => 'RM (RM)',
      ],
      'ro' => [
        'code' => 'ro',
        'dir' => 'ltr',
        'name' => 'Rom??n??',
        'base' => 'ro',
        'short_name' => 'RO',
      ],
      'ro-RO' => [
        'code' => 'ro-RO',
        'dir' => 'ltr',
        'name' => 'Rom??n?? (Rom??nia)',
        'base' => 'ro',
        'short_name' => 'RO (RO)',
      ],
      'ru' => [
        'code' => 'ru',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'ru',
        'short_name' => 'RU',
      ],
      'ru-RU' => [
        'code' => 'ru-RU',
        'dir' => 'ltr',
        'name' => '?????????????? (????????????)',
        'base' => 'ru',
        'short_name' => 'RU (RU)',
      ],
      'sa' => [
        'code' => 'sa',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'sa',
        'short_name' => 'SA',
      ],
      'sa-SA' => [
        'code' => 'sa-SA',
        'dir' => 'ltr',
        'name' => '?????????????????????',
        'base' => 'sa',
        'short_name' => 'SA (SA)',
      ],
      'sc' => [
        'code' => 'sc',
        'dir' => 'ltr',
        'name' => 'Sardu',
        'base' => 'sc',
        'short_name' => 'SC',
      ],
      'sc-SC' => [
        'code' => 'sc-SC',
        'dir' => 'ltr',
        'name' => 'Sardu',
        'base' => 'sc',
        'short_name' => 'SC (SC)',
      ],
      'sd' => [
        'code' => 'sd',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'sd',
        'short_name' => 'SD',
      ],
      'sd-SD' => [
        'code' => 'sd-SD',
        'dir' => 'rtl',
        'name' => '??????????',
        'base' => 'sd',
        'short_name' => 'SD (SD)',
      ],
      'sg' => [
        'code' => 'sg',
        'dir' => 'ltr',
        'name' => 'Sango',
        'base' => 'sg',
        'short_name' => 'SG',
      ],
      'sg-SG' => [
        'code' => 'sg-SG',
        'dir' => 'ltr',
        'name' => 'Sango',
        'base' => 'sg',
        'short_name' => 'SG (SG)',
      ],
      'sk' => [
        'code' => 'sk',
        'dir' => 'ltr',
        'name' => 'Sloven??ina',
        'base' => 'sk',
        'short_name' => 'SK',
      ],
      'sk-SK' => [
        'code' => 'sk-SK',
        'dir' => 'ltr',
        'name' => 'Sloven??ina (Slovensk?? republika)',
        'base' => 'sk',
        'short_name' => 'SK (SK)',
      ],
      'sl' => [
        'code' => 'sl',
        'dir' => 'ltr',
        'name' => 'Sloven????ina',
        'base' => 'sl',
        'short_name' => 'SL',
      ],
      'sl-SI' => [
        'code' => 'sl-SI',
        'dir' => 'ltr',
        'name' => 'Sloven????ina (Slovenija)',
        'base' => 'sl',
        'short_name' => 'SL (SI)',
      ],
      'so' => [
        'code' => 'so',
        'dir' => 'ltr',
        'name' => 'Af Somali',
        'base' => 'so',
        'short_name' => 'SO',
      ],
      'so-SO' => [
        'code' => 'so-SO',
        'dir' => 'ltr',
        'name' => 'Af Somali',
        'base' => 'so',
        'short_name' => 'SO (SO)',
      ],
      'sq' => [
        'code' => 'sq',
        'dir' => 'ltr',
        'name' => 'Shqipe',
        'base' => 'sq',
        'short_name' => 'SQ',
      ],
      'sq-AL' => [
        'code' => 'sq-AL',
        'dir' => 'ltr',
        'name' => 'Shqipe',
        'base' => 'sq',
        'short_name' => 'SQ (AL)',
      ],
      'sr' => [
        'code' => 'sr',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'sr',
        'short_name' => 'SR',
      ],
      'sr-BA' => [
        'code' => 'sr-BA',
        'dir' => 'ltr',
        'name' => '???????????? (?????????? ?? ??????????????????????)',
        'base' => 'sr',
        'short_name' => 'SR (BA)',
      ],
      'sr-CS' => [
        'code' => 'sr-CS',
        'dir' => 'ltr',
        'name' => '???????????? (???????????? ?? ???????? ????????)',
        'base' => 'sr',
        'short_name' => 'SR (CS)',
      ],
      'sr-RS' => [
        'code' => 'sr-RS',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'sr',
        'short_name' => 'SR (RS)',
      ],
      'sv' => [
        'code' => 'sv',
        'dir' => 'ltr',
        'name' => 'Svenska',
        'base' => 'sv',
        'short_name' => 'SV',
      ],
      'sv-SE' => [
        'code' => 'sv-SE',
        'dir' => 'ltr',
        'name' => 'Svenska (Sverige)',
        'base' => 'sv',
        'short_name' => 'SV (SE)',
      ],
      'sw' => [
        'code' => 'sw',
        'dir' => 'ltr',
        'name' => 'Kiswahili',
        'base' => 'sw',
        'short_name' => 'SW',
      ],
      'sw-SW' => [
        'code' => 'sw-SW',
        'dir' => 'ltr',
        'name' => 'Kiswahili',
        'base' => 'sw',
        'short_name' => 'SW (SW)',
      ],
      'ta' => [
        'code' => 'ta',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'ta',
        'short_name' => 'TA',
      ],
      'ta-TA' => [
        'code' => 'ta-TA',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'ta',
        'short_name' => 'TA (TA)',
      ],
      'th' => [
        'code' => 'th',
        'dir' => 'ltr',
        'name' => '?????????',
        'base' => 'th',
        'short_name' => 'TH',
      ],
      'th-TH' => [
        'code' => 'th-TH',
        'dir' => 'ltr',
        'name' => '????????? (???????????????????????????)',
        'base' => 'th',
        'short_name' => 'TH (TH)',
      ],
      'tig' => [
        'code' => 'tig',
        'dir' => 'ltr',
        'name' => 'Tigr??',
        'base' => 'tig',
        'short_name' => 'TIG',
      ],
      'tig-TIG' => [
        'code' => 'tig-TIG',
        'dir' => 'ltr',
        'name' => 'Tigr??',
        'base' => 'tig',
        'short_name' => 'TIG (TIG)',
      ],
      'tk' => [
        'code' => 'tk',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'tk',
        'short_name' => 'TK',
      ],
      'tk-TK' => [
        'code' => 'tk-TK',
        'dir' => 'ltr',
        'name' => '??????????????',
        'base' => 'tk',
        'short_name' => 'TK (TK)',
      ],
      'tlh' => [
        'code' => 'tlh',
        'dir' => 'ltr',
        'name' => 'tlhIngan Hol',
        'base' => 'tlh',
        'short_name' => 'TLH',
      ],
      'tlh-TLH' => [
        'code' => 'tlh-TLH',
        'dir' => 'ltr',
        'name' => 'tlhIngan Hol',
        'base' => 'tlh',
        'short_name' => 'TLH (TLH)',
      ],
      'tr' => [
        'code' => 'tr',
        'dir' => 'ltr',
        'name' => 'T??rk??e',
        'base' => 'tr',
        'short_name' => 'TR',
      ],
      'tr-TR' => [
        'code' => 'tr-TR',
        'dir' => 'ltr',
        'name' => 'T??rk??e',
        'base' => 'tr',
        'short_name' => 'TR (TR)',
      ],
      'uk' => [
        'code' => 'uk',
        'dir' => 'ltr',
        'name' => '????????????????????',
        'base' => 'uk',
        'short_name' => 'UK',
      ],
      'uk-UA' => [
        'code' => 'uk-UA',
        'dir' => 'ltr',
        'name' => '????????????????????',
        'base' => 'uk',
        'short_name' => 'UK (UA)',
      ],
      've' => [
        'code' => 've',
        'dir' => 'ltr',
        'name' => 'Tshivenda',
        'base' => 've',
        'short_name' => 'VE',
      ],
      've-VE' => [
        'code' => 've-VE',
        'dir' => 'ltr',
        'name' => 'Tshivenda',
        'base' => 've',
        'short_name' => 'VE (VE)',
      ],
      'vi' => [
        'code' => 'vi',
        'dir' => 'ltr',
        'name' => 'Ti???ng Vi???t',
        'base' => 'vi',
        'short_name' => 'VI',
      ],
      'vi-VN' => [
        'code' => 'vi-VN',
        'dir' => 'ltr',
        'name' => 'Ti???ng Vi???t',
        'base' => 'vi',
        'short_name' => 'VI (VN)',
      ],
      'vo' => [
        'code' => 'vo',
        'dir' => 'ltr',
        'name' => 'Volap??k',
        'base' => 'vo',
        'short_name' => 'VO',
      ],
      'vo-VO' => [
        'code' => 'vo-VO',
        'dir' => 'ltr',
        'name' => 'Volap??k',
        'base' => 'vo',
        'short_name' => 'VO (VO)',
      ],
      'wa' => [
        'code' => 'wa',
        'dir' => 'ltr',
        'name' => 'Walon',
        'base' => 'wa',
        'short_name' => 'WA',
      ],
      'wa-WA' => [
        'code' => 'wa-WA',
        'dir' => 'ltr',
        'name' => 'Walon',
        'base' => 'wa',
        'short_name' => 'WA (WA)',
      ],
      'xh' => [
        'code' => 'xh',
        'dir' => 'ltr',
        'name' => 'isiXhosa',
        'base' => 'xh',
        'short_name' => 'XH',
      ],
      'xh-XH' => [
        'code' => 'xh-XH',
        'dir' => 'ltr',
        'name' => 'isiXhosa',
        'base' => 'xh',
        'short_name' => 'XH (XH)',
      ],
      'yi' => [
        'code' => 'yi',
        'dir' => 'rtl',
        'name' => '????????????',
        'base' => 'yi',
        'short_name' => 'YI',
      ],
      'yi-YI' => [
        'code' => 'yi-YI',
        'dir' => 'rtl',
        'name' => '????????????',
        'base' => 'yi',
        'short_name' => 'YI (YI)',
      ],
      'zh' => [
        'code' => 'zh',
        'dir' => 'ltr',
        'name' => '??????',
        'base' => 'zh',
        'short_name' => 'ZH',
      ],
      // hans
      'zh-CN' => [
        'code' => 'zh-CN',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'zh',
        'short_name' => 'ZH (CN)',
      ],
      'zh-HK' => [
        'code' => 'zh-HK',
        'dir' => 'ltr',
        'name' => '?????? (??????)',
        'base' => 'zh',
        'short_name' => 'ZH (HK)',
      ],
      'zh-SG' => [
        'code' => 'zh-SG',
        'dir' => 'ltr',
        'name' => '?????? (?????????)',
        'base' => 'zh',
        'short_name' => 'ZH (SG)',
      ],
      // hant
      'zh-TW' => [
        'code' => 'zh-TW',
        'dir' => 'ltr',
        'name' => '????????????',
        'base' => 'zh',
        'short_name' => 'ZH (TW)',
      ],
      'zu' => [
        'code' => 'zu',
        'dir' => 'ltr',
        'name' => 'isiZulu',
        'base' => 'zu',
        'short_name' => 'ZU',
      ],
      'zu-ZU' => [
        'code' => 'zu-ZU',
        'dir' => 'ltr',
        'name' => 'isiZulu',
        'base' => 'zu',
        'short_name' => 'ZU (ZU)',
      ],
    ];
    }
}
