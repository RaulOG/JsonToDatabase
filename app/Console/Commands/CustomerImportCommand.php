<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JsonToDatabase\Customer\Exception\ImportException;
use JsonToDatabase\Customer\Service\CustomerImportService;

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
        } catch (ImportException $e) {
            $this->error($e->getMessage());
        }
    }
}
