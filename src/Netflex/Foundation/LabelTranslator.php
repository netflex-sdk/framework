<?php

namespace Netflex\Foundation;

use Exception;

use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\Translator;
use Netflex\API\Facades\API;

class LabelTranslator extends Translator
{
  /**
   * Get the translation for the given key.
   *
   * @param  string  $key
   * @param  array  $replace
   * @param  string|null  $locale
   * @param  bool  $fallback
   * @return string|array
   */
  public function get($key, array $replace = [], $locale = null, $fallback = true)
  {
    $locale = $locale ?: $this->locale;

    $matches = [];
    if (preg_match('/validation\.values\.([\w-]+)\.(.+)/', $key, $matches)) {
      $key = "validation.values.{$matches[1]}.:value";
      if (!array_key_exists('value', $replace)) {
        $replace['value'] = $matches[2];
      }
    }

    // For JSON translations, there is only one file per locale, so we will simply load
    // that file and then we will be ready to check the array for the key. These are
    // only one level deep so we do not need to do any fancy searching through it.
    $this->load('*', '*', $locale);

    $line = $this->loaded['*']['*'][$locale][$key] ?? null;

    // If we can't find a translation for the JSON key, we will attempt to translate it
    // using the typical translation file. This way developers can always just use a
    // helper such as __ instead of having to pick between trans or __ with views.
    if (!isset($line)) {
      [$namespace, $group, $item] = $this->parseKey($key);

      // Here we will get the locale that should be used for the language line. If one
      // was not passed, we will use the default locales which was given to us when
      // the translator was instantiated. Then, we can load the lines and return.
      $locales = $fallback ? $this->localeArray($locale) : [$locale];

      foreach ($locales as $locale) {
        if (!is_null($line = $this->getLine(
          $namespace,
          $group,
          $locale,
          $item,
          $replace
        ))) {
          return $line ?? $key;
        }
      }
    }

    if (!isset($line)) {
      try {
        API::post('foundation/labels', [
          'label' => base64_encode($key)
        ]);
        Cache::forget('labels');
        Label::all();
      } catch (Exception $e) {
        return $this->makeReplacements($line ?: $key, $replace);
      }
    }

    // If the line doesn't exist, we will return back the key which was requested as
    // that will be quick to spot in the UI if language keys are wrong or missing
    // from the application's language files. Otherwise we can return the line.
    return $this->makeReplacements($line ?: $key, $replace);
  }

  protected function makeReplacements($line, array $replace)
  {
    return parent::makeReplacements(
      $line,
      array_map(fn ($value) => (string) $value, $replace)
    );
  }
}
