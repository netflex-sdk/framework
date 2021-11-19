<?php

namespace Netflex\Render;

use Netflex\Render\Contracts\ImageRenderer;

class PNG extends ImageRenderer
{
    /** @var string Format */
    protected $format = 'png';

    /**
     * Preserves transparancy in output
     *
     * @param boolean $transparent
     * @return static
     */
    public function transparent(bool $transparent = true)
    {
        return $this->setOption('omitBackground', $transparent);
    }
}
