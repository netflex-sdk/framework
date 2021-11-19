<?php

namespace Netflex\Http\Concerns;

use Psr\Http\Message\ResponseInterface;

trait ParsesResponse
{
  /**
   * @param ResponseInterface $response
   * @return mixed
   */
  protected function parseResponse(ResponseInterface $response, $assoc = false)
  {
    $body = $response->getBody();

    $contentType = strtolower($response->getHeaderLine('Content-Type'));

    if (strpos($contentType, 'json') !== false) {
      $jsonBody = json_decode($body, $assoc);

      if (json_last_error() === JSON_ERROR_NONE) {
        return $jsonBody;
      }
    }

    if (strpos($contentType, 'text') !== false) {
      return $body->getContents();
    }

    return null;
  }
}
