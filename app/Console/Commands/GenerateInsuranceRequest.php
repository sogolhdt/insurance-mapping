<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleXMLElement;

class GenerateInsuranceRequest extends Command
{
    protected $signature = 'generate:insurance-xml {inputFile} {outputFile?}';
    protected $description = 'Generate an XML request for ACME insurance provider from a JSON input file';

    public function handle(): int
    {
        $inputFile = $this->argument('inputFile');

        if (!Storage::exists($inputFile)) {
            $this->error("File not found: $inputFile");
            return 1;
        }

        $inputData = json_decode(Storage::get($inputFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON format.');
            return 1;
        }

        // Validate input data to ensure correct mapping and avoid errors.
        $validator = Validator::make($inputData, [
            'holder' => 'required|in:CONDUCTOR_PRINCIPAL,OTHER',
            'occasionalDriver' => 'required|in:SI,NO',
            'prevInsurance_years' => 'required|integer',
            'prevInsurance_exists' => 'required|in:SI,NO',
        ]);

        if ($validator->fails()) {
            $this->error('Invalid input data.');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $xmlOutput = $this->generateXmlRequest($inputData);
        $outputFile = $this->argument('outputFile') ?? 'insurance_request.xml';
        Storage::put($outputFile, $xmlOutput);

        $this->info("Insurance request XML generated successfully at: $outputFile");

        return 0;
    }

    /**
     * Maps JSON input data to an XML structure.
     *
     * Mapping Details:
     *
     * | JSON Field            | XML Element             | Mapping Logic                                                            |
     * |-----------------------|-------------------------|--------------------------------------------------------------------------|
     * | holder                | CondPpalEsTomador       | 'S' if value is 'CONDUCTOR_PRINCIPAL'; otherwise 'N'.                    |
     * | occasionalDriver      | ConductorUnico          | 'S' if value is 'NO'; otherwise 'N'.                                     |
     * | FecCot (current time) | FecCot                  | Automatically set to the current date and time.                        |
     * | prevInsurance_years   | AnosSegAnte             | Directly mapped (integer).                                      |
     * | occasionalDriver      | NroCondOca              | '1' if value is 'SI'; otherwise '0'.                                     |
     * | prevInsurance_exists  | SeguroEnVigor           | 'S' if value is 'SI'; otherwise 'N'.                                     |
     *
     * @param array $inputData
     * @return string
     */
    protected function generateXmlRequest(array $inputData): string
    {
        $xml = new SimpleXMLElement('<TarificacionThirdPartyRequest></TarificacionThirdPartyRequest>');
        $datos = $xml->addChild('Datos');
        $datosGenerales = $datos->addChild('DatosGenerales');

        // Map the 'holder' field
        $datosGenerales->addChild(
            'CondPpalEsTomador',
            $inputData['holder'] === 'CONDUCTOR_PRINCIPAL' ? 'S' : 'N'
        );

        // Map the 'occasionalDriver' field for ConductorUnico
        $datosGenerales->addChild(
            'ConductorUnico',
            $inputData['occasionalDriver'] === 'NO' ? 'S' : 'N'
        );

        // Add the current date and time
        $datosGenerales->addChild('FecCot', now()->format('Y-m-d\TH:i:s'));

        // Map the 'prevInsurance_years' field
        $datosGenerales->addChild('AnosSegAnte', $inputData['prevInsurance_years']);

        // Map the 'occasionalDriver' field for NroCondOca
        $datosGenerales->addChild(
            'NroCondOca',
            $inputData['occasionalDriver'] === 'SI' ? '1' : '0'
        );

        // Map the 'prevInsurance_exists' field
        $datosGenerales->addChild(
            'SeguroEnVigor',
            $inputData['prevInsurance_exists'] === 'SI' ? 'S' : 'N'
        );

        return $xml->asXML();
    }
}
