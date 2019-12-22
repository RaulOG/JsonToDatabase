<?php

namespace App\Console\Commands;

use App\Customer;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Console\Command;
use pcrov\JsonReader\InputStream\IOException;
use pcrov\JsonReader\JsonReader;
use pcrov\JsonReader\Parser\ParseException;

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

        $reader = new JsonReader();

        try {
            $reader->open($filePath);
            $reader->read(); // Enter in array of customers
            $reader->read(); // Reader index at the customer 0
        } catch (IOException $e) {
            $this->error('File not found');
            return;
        } catch (ParseException $e) {
            $this->error('File is empty');
            return;
        }

        $count = 0;
        do {
            $transformedData = $this->transformRaw($reader->value());
            $customer = new Customer($transformedData);
            $customer->save();
            $count++;
        } while ($reader->next() && !is_null($reader->value()) && $count < 10);


        $reader->close();
    }

    private function transformRaw(array $raw): array
    {
        return [
            "name" => $raw["name"],
            "address" => $raw["address"],
            "checked" => (boolean)$raw["checked"],
            "description" => $raw["description"],
            "interest" => $raw["interest"],
            "date_of_birth" => $this->getDateOfBirth($raw["date_of_birth"]),
            "email" => $raw["email"],
            "account" => $raw["account"],
            "credit_card_type" => $raw["credit_card"]["type"],
            "credit_card_number" => $raw["credit_card"]["number"],
            "credit_card_name" => $raw["credit_card"]["name"],
            "credit_card_expiration_date" => $raw["credit_card"]["expirationDate"],
        ];
    }

    /**
     * @param string $dateOfBirth
     * @return null|string
     */
    private function getDateOfBirth($dateOfBirth)
    {
        if (is_null($dateOfBirth)) {
            return null;
        }

        if ($this->dateHasSlashes($dateOfBirth)) {
            return $this->parseSlashedDate($dateOfBirth);
        }

        return Carbon::create($dateOfBirth)->format("Y-m-d");
    }

    private function dateHasSlashes($dateOfBirth): bool
    {
        return (bool)strpos($dateOfBirth, '/');
    }

    /**
     * Assuming slashed dates have always the format d-m-Y
     *
     * @param $dateOfBirth
     * @return string
     */
    private function parseSlashedDate($dateOfBirth)
    {
        return Carbon::createFromFormat('d/m/Y', $dateOfBirth)->format("Y-m-d");
    }
}
