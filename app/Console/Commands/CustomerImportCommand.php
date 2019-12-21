<?php

namespace App\Console\Commands;

use App\Customer;
use ErrorException;
use Illuminate\Console\Command;

class CustomerImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads a JSON file and writes the customer entries into DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fileName = $this->argument('file');
        $filePath = base_path($fileName);

        try{
            $jsonString = file_get_contents($filePath);
        }catch(ErrorException $e){
            $this->error('File not found');
            return ;
        }

        $jsonObject = json_decode($jsonString, true);
        $customers = collect($jsonObject);

        $customers->each(function(){
            $customer = new Customer();
            $customer->save();
        });
    }
}
