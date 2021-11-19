<?php

namespace Netflex\Foundation;

use Illuminate\Translation\FileLoader;

class LabelLoader extends FileLoader
{
  public function loadLabels()
  {
    return Label::all();
  }

  public function loadJsonPaths($locale)
  {
    return Label::all()[$locale] ?? collect();
  }
}
