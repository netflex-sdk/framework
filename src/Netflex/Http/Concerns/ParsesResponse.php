<?php

namespace Netflex\Http\Concerns;

use Psr\Http\Message\ResponseInterface;

trait ParsesResponse
{
  /**
   * @param ResponseInterface $response
   * @param bool $assoc
   * @return mixed
   */
  protected function parseResponse(
    ResponseInterface $response,
    bool $assoc = false,
  ): mixed {
    $body = $response->getBody();

    $contentType = strtolower($response->getHeaderLine('Content-Type'));

    if (str_contains($contentType, 'json')) {
      $jsonBody = json_decode(preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
        return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
      }, (string) $body), $assoc);

      if (json_last_error() === JSON_ERROR_NONE) {
        return $jsonBody;
      }
    }

    if (str_contains($contentType, 'text')) {
      return $body->getContents();
    }

    return null;
  }
}
