# Generate Insurance XML Command

## Overview
This Laravel command-line tool reads customer data from a JSON file, validates it, and converts it into an XML request format for the ACME insurance provider.

## Features
- Reads input from a JSON file.
- Validates required fields.
- Maps JSON fields to the expected XML format.
- Saves the generated XML file to storage.
- Provides error handling for missing files, invalid JSON, and incorrect data.

## Installation
Ensure your Laravel project is set up and dependencies are installed:
```sh
composer install
```

## Usage
Run the command with:
```sh
php artisan generate:insurance-xml {inputFile} {outputFile?}
```
- `inputFile` (required): Path to the JSON input file.
- `outputFile` (optional): Path to save the generated XML file. Defaults to `insurance_request.xml`.

## Example
### JSON Input File (`input.json`)
```json
{
  "holder": "CONDUCTOR_PRINCIPAL",
  "occasionalDriver": "NO",
  "prevInsurance_years": 5,
  "prevInsurance_exists": "SI"
}
```
Run the command:
```sh
php artisan generate:insurance-xml input.json output.xml
```
### Generated XML Output (`output.xml`)
```xml
<TarificacionThirdPartyRequest>
    <Datos>
        <DatosGenerales>
            <CondPpalEsTomador>S</CondPpalEsTomador>
            <ConductorUnico>S</ConductorUnico>
            <FecCot>2025-02-07T12:00:00</FecCot>
            <AnosSegAnte>5</AnosSegAnte>
            <NroCondOca>0</NroCondOca>
            <SeguroEnVigor>S</SeguroEnVigor>
        </DatosGenerales>
    </Datos>
</TarificacionThirdPartyRequest>
```

## Validation Rules
- `holder`: Required, must be either `CONDUCTOR_PRINCIPAL` or `OTHER`.
- `occasionalDriver`: Required, must be `SI` or `NO`.
- `prevInsurance_years`: Optional, must be an integer.
- `prevInsurance_exists`: Required, must be `SI` or `NO`.

## Error Handling
- If the file does not exist, the command outputs: `File not found: {inputFile}`.
- If the JSON format is invalid, the command outputs: `Invalid JSON format.`
- If validation fails, it outputs: `Invalid input data.` along with specific errors.

## Running Tests
This project includes feature tests to ensure functionality:
```sh
php artisan test
```
### Test Cases
1. **Fails when file does not exist.**
2. **Fails when JSON is invalid.**
3. **Fails when input data is invalid.**
4. **Generates XML successfully with valid data.**

## License
This project is licensed under the MIT License.

