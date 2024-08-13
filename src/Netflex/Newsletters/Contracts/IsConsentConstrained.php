<?php

namespace Netflex\Newsletters\Contracts;

interface IsConsentConstrained
{

  /**
   * Returns a list of consents of which one needs to be active in order for the mail to be sent.
   * @return array<int>
   */
  public function requiredConsents(): array;
}
