<?php

$add_langs = array_slice($argv, 1);
$locales_path = __DIR__ . '/vendor/umpirsky/locale-list/data';
$out_file_path = __DIR__ . '/locales.php';

//

$en_locales = require $locales_path . '/en/locales.php';

$add_locales = [];
foreach ($add_langs as $add_lang) {
    if (strlen($add_lang) == 2 && preg_match('/^[a-z]{2}$/', $add_lang) && $add_lang != 'en') {
        $add_locales_path = $locales_path . '/' . $add_lang . '/locales.php';
        $add_locales[$add_lang] = is_file($add_locales_path) ? require $add_locales_path : [];
    }
}

$out = [];

foreach ($en_locales as $locale => $name) {
    if (strlen($locale) == 5 && preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) && is_file($locales_path . '/' . $locale . '/locales.php')) {
        $locale_locales = require $locales_path . '/' . $locale . '/locales.php';
        $short_locale = substr($locale, 0, 2);
        if (isset($locale_locales[$locale], $locale_locales[$short_locale], $en_locales[$short_locale])) {
            $locale_dash = str_replace('_', '-', $locale);
            $out[$locale_dash] = [
                'name' => $locale_locales[$short_locale],
                'full_name' => $locale_locales[$locale],
            ];
            if (in_array('en', $add_langs)) {
                $out[$locale_dash]['name_en'] = $en_locales[$short_locale];
                $out[$locale_dash]['full_name_en'] = $name;
            }
            foreach ($add_langs as $add_lang) {
                if (isset($add_locales[$add_lang][$locale], $add_locales[$add_lang][$short_locale])) {
                    $out[$locale_dash]['name_' . $add_lang] = $add_locales[$add_lang][$short_locale];
                    $out[$locale_dash]['full_name_' . $add_lang] = $add_locales[$add_lang][$locale];
                }
            }
        }
    }
}

$str = var_export($out, true) . ";";
$str = strtr($str, [
    "=> \n  array (" => "=> [",
    ")," => "],",
    "array (" => "[",
    ");" => "];",
    "    " => "\t\t",
    "  " => "\t",
]);

file_put_contents($out_file_path, "<?php\nreturn $str\n");
