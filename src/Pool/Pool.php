<?php

namespace Wulsic\Pool;

use Closure;
use parallel\Future;
use parallel\Runtime;

/**
 * Resembles parts of the Pthreads pool functionality. Still a w.i.p.
 * Inspired by Krakjoe's parallel implementation and gist
 * https://gist.github.com/krakjoe/254897be71d23b5d5ac2d436f52e8d7d
 *
 * @package Monitor\Parallel
 */
class Pool implements PoolInterface
{
    /** @var Runtime[] */
    private $workers = [];

    /** @var Future[] */
    private $jobs = [];

    /** @var int */
    private $worker = 0;

    public function __construct(int $workers, string $bootstrap = null)
    {
        for ($i = 0; $i < $workers; $i++) {
            if ($bootstrap) {
                $this->workers[$i] = new Runtime($bootstrap);
            } else {
                $this->workers[$i] = new Runtime();
            }
        }
    }

    /**
     * Execute the collector on each future in the futures collection for return value modifications.
     * The collect will return a status indication for the current active jobs.
     *
     * @return array
     */
    public function collect()
    {
        foreach ($this->jobs as $id => $job) {
            // In PThreads the collector was given to the task instead of handled in the pool itself.
            // Todo: Add retry functionality to retry cancelled jobs?
            // Todo: Add collector back.
            if ($job->cancelled() || $job->done()) {
                // Clean-up resources.
                unset($this->jobs[$id]);
            }
        }

        return count($this->jobs) !== 0;
    }

    /**
     * https://github.com/krakjoe/pthreads/blob/master/classes/pool.h
     * The tasks combined with the arguments will be a "job" for the "worker" to work on.
     *
     * @param Closure $task
     * @param array   $args
     *
     * @return void
     * @todo: Implement future active checking to spread the workers.
     *
     */
    public function submit(\Closure $task, array $args = []) : void
    {
        if ($this->worker >= count($this->workers)) {
            $this->worker = 0;
        }

        $this->jobs[] = $this->workers[$this->worker++]->run($task, $args);
    }

    /**
     * Force the pool to shutdown.
     */
    private function __destruct()
    {
        foreach ($this->workers as $worker) {
            $worker->kill();
        }
    }

    /**
     * The pool can be shutdown using two options.
     * Finish = true (default). Gracefully shutdown the pool after all the jobs are executed.
     * Finish = false. Force shutdown the pool by killing the workers.
     *
     * @param bool $finish
     */
    public function shutdown(bool $finish = true)
    {
        foreach ($this->workers as $worker) {
            $finish ? $worker->close() : $worker->kill();
        }
    }
}