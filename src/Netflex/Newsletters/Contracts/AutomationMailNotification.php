<?php

namespace Netflex\Newsletters\Contracts;

use Netflex\Newsletters\AutomationMailMessage;

interface AutomationMailNotification
{
  public function toAutomationMail(): AutomationMailMessage;
}
