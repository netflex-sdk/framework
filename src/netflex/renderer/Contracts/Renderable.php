<?php

namespace Netflex\Render\Contracts;

use Illuminate\Http\Response;
use Illuminate\Contracts\Support\Responsable;

interface Renderable extends Responsable
{
    /**
     * @return resource
     */
    public function stream();

    /**
     * @param string $filename
     * @return Response
     */
    public function download($filename = null): Response;

    /**
     * @return string
     */
    public function link(): string;

    /**
     * @return string|null
     */
    public function blob();
}
