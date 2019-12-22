<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerImportCommandTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example
     *
     * @test
     */
    public function it_outputs_error_message_when_file_is_not_found()
    {
        $file = 'tests/Support/jsons/404.json';

        $command = $this->artisan('customer:import', ['file' => $file]);

        $command->expectsOutput('File not found');
    }

    /**
     * A basic test example.
     *
     * @test
     */
    public function it_does_nothing_when_file_is_empty()
    {
        $file = 'tests/Support/jsons/sample_without_data.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseMissing('customers', []);
    }

    /**
     * @test
     */
    public function it_writes_one_entry_when_file_contains_one_row()
    {
        $file = 'tests/Support/jsons/sample_with_one_entry.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseHas('customers', ['id' => 1]);
        $this->assertDatabaseMissing('customers', ['id' => 2]);
    }

    /**
     * @test
     */
    public function it_writes_multiple_entries_when_file_contains_multiple_rows()
    {
        $file = 'tests/Support/jsons/sample_with_multiple_entries.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseHas('customers', ['id' => 1]);
        $this->assertDatabaseHas('customers', ['id' => 2]);
        $this->assertDatabaseHas('customers', ['id' => 3]);
    }

    /**
     * @test
     */
    public function it_writes_a_customer_entry_with_expected_data_structure()
    {
        $file = 'tests/Support/jsons/sample_with_one_entry.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' =>  "Prof. Simeon Green",
            'address' =>  "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' =>  (int)false,
            'description' =>  "Voluptatibus nihil dolor quaerat.",
            'interest' =>  "enable 24/7 channels",
            'date_of_birth' =>  "1989-03-21T01:11:13+00:00",
            'email' =>  "nerdman@cormier.net",
            'account' =>  "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' =>"4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /**
     * @test
     */
    public function it_allows_interest_to_be_null()
    {
        $file = 'tests/Support/jsons/sample_with_null_interest.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' =>  "Prof. Simeon Green",
            'address' =>  "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' =>  (int)false,
            'description' =>  "Voluptatibus nihil dolor quaerat.",
            'interest' =>  null,
            'date_of_birth' =>  "1989-03-21T01:11:13+00:00",
            'email' =>  "nerdman@cormier.net",
            'account' =>  "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' =>"4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }
}
