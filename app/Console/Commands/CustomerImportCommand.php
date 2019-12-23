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
     * @var JsonReader $reader
     */
    private $reader;

    /**
     * Defines the iteration step reading the json
     * @var $count
     */
    private $count;

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

        $this->reader = new JsonReader();

        try {
            $this->reader->open($filePath);
            $this->reader->read(); // Enter in array of customers
            $this->reader->read(); // Reader index at the customer 0
        } catch (IOException $e) {
            $this->error('File not found');
            return;
        } catch (ParseException $e) {
            $this->error('File is empty');
            return;
        }

        $this->count = 1;

        if($this->hasProcessAlreadyStartedForGivenFileName($fileName))
        {
            $numberOfCustomersAlreadyStored = Customer::where(["filename" => $fileName])->max("count");

            while($numberOfCustomersAlreadyStored > 0)
            {
                $this->reader->next();
                $this->count++;
                $numberOfCustomersAlreadyStored--;
            }
        }

        while(!$this->isProcessFinished()){
            $transformedData = $this->prepareData($this->reader->value(), $this->count, $fileName);
            $customer = new Customer($transformedData);
            $customer->save();
            $this->count++;

            $this->reader->next();
        }

        $this->reader->close();
    }

    private function prepareData(array $raw, int $counter, string $fileName): array
    {
        return array_merge([
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
        ], [
            'count' => $counter,
            'filename' => $fileName
        ]);
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

    /**
     * @param string $fileName
     * @return bool
     */
    private function hasProcessAlreadyStartedForGivenFileName(string $fileName): bool
    {
        return Customer::where("filename", $fileName)->exists();
    }

    /**
     * @return bool
     */
    private function isProcessFinished(): bool
    {
        return is_null($this->reader->value());
    }
}
