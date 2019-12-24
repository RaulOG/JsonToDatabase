<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JsonToDatabase\Customer\Service\CustomerImportService;
use JsonToDatabase\Reader\Exception\ReaderException;

class CustomerImportCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'customer:import {file}';

    /**
     * @var string
     */
    protected $description = 'Reads a JSON file and writes the customer entries into DB';

    public function handle(CustomerImportService $service): void
    {
        try {
            $service->run($this->argument("file"));
        } catch (ReaderException $e) {
            $this->error($e->getMessage());
        }
    }
}
