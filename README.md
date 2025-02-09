# Generate Insurance Request Command

This Laravel command generates an XML request for the ACME insurance provider based on input customer parameters from a JSON file.

## Installation
1. Clone the repository and navigate to the project root.
2. Ensure Laravel is set up with dependencies installed:
   ```sh
   composer install
   ```
3. Copy the `.env.example` file to `.env` and configure the necessary environment variables:
   ```sh
   cp .env.example .env
   ```
4. Generate the application key:
   ```sh
   php artisan key:generate
   ```


## Usage
1. Create a JSON file with customer data inside `storage/app/private/`.
## Input JSON Format
Example:
```json
{
  "holder": "CONDUCTOR_PRINCIPAL",
  "occasionalDriver": "NO",
  "prevInsurance_years": 5,
  "prevInsurance_exists": "SI"
}
```
2. Run the command:
   ```sh
   php artisan generate:insurance-xml input.json output.xml
   ```
   If no output file is specified, it defaults to `insurance_request.xml`.


## Validation Rules
- `holder`: required, allowed values: `CONDUCTOR_PRINCIPAL`, `OTHER`
- `occasionalDriver`: required, allowed values: `SI`, `NO`
- `prevInsurance_years`: required, integer
- `prevInsurance_exists`: required, allowed values: `SI`, `NO`

## Error Handling
- Displays an error if the input file is missing or invalid JSON.
- Displays validation errors if data is incorrect.

## Testing
Run the test suite:
```sh
php artisan test
```
The tests cover:
- Missing file handling
- Invalid JSON handling
- Validation failures
- Successful XML generation

## Output XML Example
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

Ensure the JSON file is placed in `storage/app/private/` before running the command.

If you clone the project, make sure to create an `.env` file as it is not included in the repository.

