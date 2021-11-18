<?php

if(!function_exists('mustache')) {
    /**
     * Renders a Mustache template
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    function mustache (string $template, array $variables = []) {
        return with(new Mustache_Engine(['entitiy_flags' => ENT_QUOTES]))
            ->render($template, $variables);
    }
}
