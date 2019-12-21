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
}
