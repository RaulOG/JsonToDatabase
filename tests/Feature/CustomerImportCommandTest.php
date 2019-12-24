<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerImportCommandTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_outputs_error_message_when_file_is_not_found()
    {
        // Arrange
        $file = 'tests/Support/jsons/404.json';

        // Act
        $command = $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $command->expectsOutput('File not found');
    }

    /**
     * @test
     */
    public function it_does_nothing_when_file_is_empty()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_without_data.json';

        // Act
        $command = $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseMissing('customers', []);
        $command->expectsOutput('File is empty');
    }

    /**
     * @test
     */
    public function it_writes_one_entry_when_file_contains_one_row()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_one_entry.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'count' => 1,
            'filename' => $file,
        ]);
        $this->assertDatabaseMissing('customers', ['id' => 2]);
    }

    /**
     * @test
     */
    public function it_writes_a_customer_entry_with_expected_data_structure()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_one_entry.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' =>  "Prof. Simeon Green",
            'address' =>  "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' =>  (int)false,
            'description' =>  "Voluptatibus nihil dolor quaerat.",
            'interest' =>  "enable 24/7 channels",
            'date_of_birth' =>  "1989-03-21",
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
     *
     * Nullable fields:
     *  interest
     *  date_of_birth
     */
    public function it_stores_customers_with_null_fields()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_nulls.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' => "Prof. Simeon Green",
            'address' => "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' => (int)false,
            'description' => "Voluptatibus nihil dolor quaerat.",
            'interest' => null,
            'date_of_birth' => null,
            'email' => "nerdman@cormier.net",
            'account' => "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' => "4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /** @test */
    public function it_stores_date_of_birth_from_a_slash_formatted_birth_of_date()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_slash_date_of_birth_as_d-m-y.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' => "Prof. Simeon Green",
            'address' => "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' => (int)false,
            'description' => "Voluptatibus nihil dolor quaerat.",
            'interest' => null,
            'date_of_birth' => "1969-10-15",
            'email' => "nerdman@cormier.net",
            'account' => "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' => "4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /** @test */
    public function it_saves_date_of_birth_as_expected_when_it_is_formatted()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_date_of_birth_with_formatted_date.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' => "Prof. Simeon Green",
            'address' => "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' => (int)false,
            'description' => "Voluptatibus nihil dolor quaerat.",
            'interest' => null,
            'date_of_birth' => "1966-07-15",
            'email' => "nerdman@cormier.net",
            'account' => "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' => "4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /** @test */
    public function it_saves_date_of_birth_as_expected_when_it_is_a_timestamp()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_timestamped_date_of_birth.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' => "Prof. Simeon Green",
            'address' => "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' => (int)false,
            'description' => "Voluptatibus nihil dolor quaerat.",
            'interest' => null,
            'date_of_birth' => "1989-03-21",
            'email' => "nerdman@cormier.net",
            'account' => "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' => "4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /** @test */
    public function it_stores_long_descriptions()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_long_description_entry.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
        $this->assertDatabaseHas('customers', [
            'id' => 1,
            'name' => "Prof. Simeon Green",
            'address' => "328 Bergstrom Heights Suite 709 49592 Lake Allenville",
            'checked' => (int)false,
            'description' => "Beatae adipisci quae dolores possimus similique impedit laudantium. Error cum totam autem est earum rem sint eos. Consequuntur molestias ipsam repellat dolorem praesentium.<br>Architecto excepturi nam neque ullam ea. Ut enim incidunt perspiciatis blanditiis porro. Sed repellat eum et error. Est hic est quidem mollitia numquam nihil suscipit.<br>Quaerat iste et et ipsam et. Dolor ut commodi eligendi iure autem nesciunt. Molestiae similique nemo enim qui qui sequi omnis. Inventore voluptate tempora vitae eius id quam libero.",
            'interest' => null,
            'date_of_birth' => "1989-03-21",
            'email' => "nerdman@cormier.net",
            'account' => "556436171909",
            'credit_card_type' => 'Visa',
            'credit_card_number' => "4532383564703",
            'credit_card_name' => "Brooks Hudson",
            'credit_card_expiration_date' => "12/19",
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_write_any_entry_from_an_already_imported_file()
    {
        // Arrange
        $file = 'tests/Support/jsons/sample_with_one_entry.json';

        // Act
        $this->artisan('customer:import', ['file' => $file]);
        $this->artisan('customer:import', ['file' => $file]);

        // Assert
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

}
