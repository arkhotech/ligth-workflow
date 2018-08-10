<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facedes\Log;

class AsyncStart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id_instance;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id_process)
    {
        $this->id_instance = $id_process;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("iniciando el trabajo: ".$this->id_instance);
        $process_instance = ProcessInstance::find($this->id_instance);
        $process_instance->start();
        //Realizar el callback
    }
}
