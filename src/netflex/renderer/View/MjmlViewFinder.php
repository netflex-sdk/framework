<?php

namespace Netflex\Render\View;

use Illuminate\View\FileViewFinder;

class MjmlViewFinder extends FileViewFinder
{
    protected $extensions = ['mjml.blade.php', 'mjml.php', 'mjml'];
}
