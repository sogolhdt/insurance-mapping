<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateInsuranceRequestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_command_fails_if_file_does_not_exist()
    {
        $this->artisan('generate:insurance-xml non_existent.json')
            ->expectsOutput('File not found: non_existent.json')
            ->assertExitCode(1);
    }

    public function test_command_fails_if_json_is_invalid()
    {
        Storage::put('invalid.json', 'invalid json');

        $this->artisan('generate:insurance-xml invalid.json')
            ->expectsOutput('Invalid JSON format.')
            ->assertExitCode(1);
    }

    public function test_command_fails_if_data_is_invalid()
    {
        $invalidData = json_encode([
            'holder' => 'INVALID_VALUE',
            'occasionalDriver' => 'SI',
            'prevInsurance_years' => 5,
            'prevInsurance_exists' => 'NO',
        ]);

        Storage::put('invalid_data.json', $invalidData);

        $this->artisan('generate:insurance-xml invalid_data.json')
            ->expectsOutput('Invalid input data.')
            ->assertExitCode(1);
    }

    public function test_command_generates_xml_successfully()
    {
        $validData = json_encode([
            'holder' => 'CONDUCTOR_PRINCIPAL',
            'occasionalDriver' => 'NO',
            'prevInsurance_years' => 5,
            'prevInsurance_exists' => 'SI',
        ]);

        Storage::put('valid_data.json', $validData);

        $this->artisan('generate:insurance-xml valid_data.json')
            ->expectsOutput('Insurance request XML generated successfully at: insurance_request.xml')
            ->assertExitCode(0);

        Storage::assertExists('insurance_request.xml');
    }
    public function test_generated_xml_structure_is_correct()
    {
        $validData = json_encode([
            'holder' => 'CONDUCTOR_PRINCIPAL',
            'occasionalDriver' => 'NO',
            'prevInsurance_years' => 5,
            'prevInsurance_exists' => 'SI',
        ]);

        Storage::put('valid_data.json', $validData);

        $this->artisan('generate:insurance-xml valid_data.json')
            ->assertExitCode(0);

        $xmlContent = Storage::get('insurance_request.xml');
        $xml = simplexml_load_string($xmlContent)->Datos;


        $this->assertEquals('S', (string) $xml->DatosGenerales->CondPpalEsTomador, "Main driver mapping is incorrect");
        $this->assertEquals('S', (string) $xml->DatosGenerales->ConductorUnico, "Single driver mapping is incorrect");
        $this->assertEquals(5, (int) $xml->DatosGenerales->AnosSegAnte, "Previous insurance years mapping is incorrect");
        $this->assertEquals('S', (string) $xml->DatosGenerales->SeguroEnVigor, "Existing insurance mapping is incorrect");
    }
}
