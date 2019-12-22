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
        $rawCustomers = collect($jsonObject);

        $rawCustomers->each(function($rawCustomer){
            $customer = new Customer($this->transformRaw($rawCustomer));
            $customer->save();
        });
    }

    private function transformRaw(array $raw): array
    {
        return [
            "name" => $raw["name"],
            "address" => $raw["address"],
            "checked" => (boolean)$raw["checked"],
            "description" => $raw["description"],
            "interest" => $raw["interest"],
            "date_of_birth" => $raw["date_of_birth"],
            "email" => $raw["email"],
            "account" => $raw["account"],
            "credit_card_type" => $raw["credit_card"]["type"],
            "credit_card_number" => $raw["credit_card"]["number"],
            "credit_card_name" => $raw["credit_card"]["name"],
            "credit_card_expiration_date" => $raw["credit_card"]["expirationDate"],
        ];
    }
}
