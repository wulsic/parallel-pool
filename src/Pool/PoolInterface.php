<?php

namespace Wulsic\Pool;

interface PoolInterface
{
    public function __construct( int $size, string $bootstrap = null);

    public function collect( \Closure $collector = null );

    public function shutdown( bool $finish = true );

    public function submit( \Closure $closure, array $args );
}