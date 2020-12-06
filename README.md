Parallel-Pool
================
This library provides a PThreads Pool like functionality while using the new Parallel library.

Example usage:
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$workers = 10;
$jobs = 1000;

$pool = new \Wulsic\Pool\Pool($workers);

echo "Worker count: $workers" . PHP_EOL;

$start = microtime(true);

for($i = 0; $i < $jobs; $i++) {
    echo "Job $i" . PHP_EOL;
    $pool->submit(
        function ($taskId) {
            print "Active task: $taskId" . PHP_EOL;
            return "Finished task: $taskId";
        },
        [$i]
    );
}

while($pool->collect()) {
    continue;
}

$pool->shutdown();

printf(
    "Finished %s threads in %.2f seconds\n",
    $workers ? "with {$workers}" : "without",
    microtime(true) - $start
);
```

License
-------

All contents of this package are licensed under the [MIT license].