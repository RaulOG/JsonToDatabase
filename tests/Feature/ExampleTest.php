<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

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
        // Json sample without data
        $file = 'tests/Support/jsons/sample_without_data.json';

        $this->artisan('customer:import', ['file' => $file]);

        $this->assertDatabaseMissing('customers', []);
    }
}
