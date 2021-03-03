<?php

namespace CloudMonitor\Toolkit\Core;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Encryption\Encrypter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Queue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The transport instance.
     *
     * @var Transportable|array
     */
    protected $transport;

    /**
     * Create a new job instance.
     *
     * @param Transportable|array $transport
     * @return void
     */
    public function __construct(mixed $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Execute the job.
     *
     * @param  Transportable  $transport
     * @return void
     */
    public function handle()
    {
        Transport::post($this->transport);
    }
}