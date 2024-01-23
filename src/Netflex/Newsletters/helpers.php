<?php

/**
 * Returns the codepoint of a multibyte char
 *
 * @param string $char
 * @param string $encoding
 * @return int
 */
function mb_convert($char, $encoding = 'UTF-8')
{
  if ($encoding === 'UCS-4BE') {
    list(, $ord) = (strlen($char) === 4) ? @unpack('N', $char) : @unpack('n', $char);
    return $ord;
  } else {
    return mb_convert(mb_convert_encoding($char, 'UCS-4BE', $encoding), 'UCS-4BE');
  }
}

/**
 * Encodes multibyte chars to HTML entity
 *
 * @param string $string
 * @param bool $hex
 * @param string $encoding
 * @return string
 */
function mb_convert_entities($string, $hex = false, $encoding = 'UTF-8')
{
  return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($match) use ($hex) {
    return sprintf($hex ? '&#x%X;' : '&#%d;', mb_convert($match[0]));
  }, $string);
}
