<?php

namespace Netflex\Render\Contracts;

use Netflex\Render\Renderer;

abstract class ImageRenderer extends Renderer
{
    /**
     * Screenshot the entire page, also content outside visible viewport
     *
     * @param boolean $fullPage
     * @return static
     */
    public function fullPage($fullPage = true)
    {
        if ($this->getOption('clip')) {
            $this->setOption('clip', null);
        }

        return $this->setOption('fullPage', $fullPage);
    }

    /**
     * Specifies a CSS selector for capturing a specific element.
     *
     * @param string $selector
     * @return static
     */
    public function selector(string $selector)
    {
        return $this->setOption('selector', $selector);
    }

    /**
     * Clip the screenshot
     *
     * @param integer $x
     * @param integer $y
     * @param integer $width
     * @param integer $height
     * @return static
     */
    public function clip(int $x, int $y, int $width, int $height)
    {
        if ($this->getOption('fullPage')) {
            $this->setOption('fullPage', false);
        }

        return $this->setOption('clip', [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height
        ]);
    }

    /**
     * Viewport width. Default 1920
     *
     * @param integer $width
     * @return static
     */
    public function width(int $width = 1920)
    {
        return $this->setOption('width', $width);
    }

    /**
     * Viewport height. Default 1080
     *
     * @param integer $width
     * @return static
     */
    public function height(int $height = 1080)
    {
        return $this->setOption('height', $height);
    }
}
