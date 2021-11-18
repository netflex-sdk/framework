<?php

namespace Netflex\Render\Mail;

use Netflex\Render\MJML as Renderer;

trait MJML
{
    /**
     * Set the view and view data for the message.
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function mjml($view, array $data = [])
    {
        return $this->html(Renderer::view($view, $data)->blob());
    }
}
