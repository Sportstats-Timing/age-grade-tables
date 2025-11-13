# Age Grade Tables for PHP

A PHP package providing age grading tables for races like Ironman and Ironman 70.3 in a compact, efficient format. Includes helpers for retrieving age grade factors by age and gender.

## Features

- **Compact Data Format**: Efficient 5-year age group tables for both genders
- **PHP 7.4+ Support**: Modern PHP with type hints and nullable types
- **Simple API**: Retrieve tables and factors with a single method call
- **Bulk Processing**: Efficient processing for large datasets (40,000+ athletes)
- **Zero Dependencies**: Lightweight and fast

## Installation

### Via Composer (Recommended)

```bash
composer require sportstats-timing/age-grade-tables
```

### Manual Installation

Download the `age-grade-tables.php` file and include it in your project:

```php
require_once 'age-grade-tables.php';
```

## Usage

### Get a Table in Array or JSON Format

```php
use SportstatsTiming\AgeGradeTables\AgeGradeTables;

// Get the 2025 Ironman 70.3 table in compact array format
$arrayFormat = AgeGradeTables::getAgeGradeTable('2025_ironman703', 'array');
print_r($arrayFormat);
// Output: [ ['M', 18, 24, 1.0000], ['M', 25, 29, 0.9929], ... ]

// Get the same table in object (JSON) format
$jsonFormat = AgeGradeTables::getAgeGradeTable('2025_ironman703', 'json');
print_r($jsonFormat);
// Output: [ ['gender' => 'M', 'start' => 18, 'end' => 24, 'factor' => 1.0000], ... ]
```

### Get a Factor by Age and Gender

```php
$table = AgeGradeTables::getAgeGradeTable('2025_ironman', 'array');
$factor = AgeGradeTables::getFactorByAgeAndGender($table, 'M', 37);
echo $factor; // 0.9895
```

### Bulk Processing for Large Datasets (40,000+ athletes)

```php
// Setup: Create fast lookup (do once)
$table = AgeGradeTables::getAgeGradeTable('2025_ironman', 'array');
$lookup = AgeGradeTables::createAgeGradeLookup($table);

// Process 40,000 athletes efficiently
$athletes = [
    ['id' => 1, 'age' => 35, 'gender' => 'M', 'finishTime' => 32400],
    ['id' => 2, 'age' => 42, 'gender' => 'F', 'finishTime' => 34200],
    // ... 40,000 athletes
];

$results = AgeGradeTables::processAthletesBulk($lookup, $athletes);
// Returns: [['id' => 1, 'age' => 35, 'gender' => 'M', 'finishTime' => 32400, 'factor' => 0.9895], ...]

// Calculate age-graded times
$ageGradedResults = [];
foreach ($results as $athlete) {
    if ($athlete['factor']) {
        $ageGradedTime = $athlete['finishTime'] / $athlete['factor'];
        $ageGradedResults[] = array_merge($athlete, ['ageGradedTime' => $ageGradedTime]);
    }
}
```

## API Reference

### Tables
- `AgeGradeTables::getAgeGradeTable(string $name, string $format = 'array')`
  - `$name`: `'2025_ironman'` or `'2025_ironman703'`
  - `$format`: `'array'` (default) or `'json'`
  - Returns: Age grade table in the requested format

### Helpers
- `AgeGradeTables::getFactorByAgeAndGender(array $table, string $gender, int $age)`
  - `$table`: Age grade table (array format)
  - `$gender`: `'M'` or `'F'`
  - `$age`: integer (athlete's age)
  - Returns: The factor for the matching age/gender, or `null` if not found

### Bulk Processing (for large datasets)
- `AgeGradeTables::createAgeGradeLookup(array $table)`
  - `$table`: Age grade table (array format)
  - Returns: Fast lookup array for O(1) factor retrieval
- `AgeGradeTables::processAthletesBulk(array $lookup, array $athletes)`
  - `$lookup`: Pre-built lookup array
  - `$athletes`: Array of athlete arrays with `age` and `gender` keys
  - Returns: Array of athletes with their age grade factors added
- `AgeGradeTables::getFactorFromLookup(array $lookup, string $gender, int $age)`
  - `$lookup`: Pre-built lookup array
  - `$gender`: `'M'` or `'F'`
  - `$age`: integer (athlete's age)
  - Returns: The factor for the matching age/gender, or `null` if not found

## Data Format

Tables are stored as compact arrays:

```php
// [gender, start_age, end_age, factor]
['M', 35, 39, 0.9895] // Males age 35-39, factor 0.9895
```

## Performance

- **Bulk processing**: ~1-5ms for 40,000 athletes
- **Individual lookups**: ~0.1-0.5ms per athlete
- **Memory usage**: ~2-5MB for lookup table

## Example: Laravel Integration

```php
// In your Laravel service or controller
use SportstatsTiming\AgeGradeTables\AgeGradeTables;

class RaceResultsService
{
    private $lookup;
    
    public function __construct()
    {
        $table = AgeGradeTables::getAgeGradeTable('2025_ironman', 'array');
        $this->lookup = AgeGradeTables::createAgeGradeLookup($table);
    }
    
    public function processRaceResults($athletes)
    {
        $results = AgeGradeTables::processAthletesBulk($this->lookup, $athletes);
        
        // Calculate age-graded times and rankings
        $ageGradedResults = [];
        foreach ($results as $athlete) {
            if ($athlete['factor']) {
                $ageGradedTime = $athlete['finishTime'] / $athlete['factor'];
                $ageGradedResults[] = array_merge($athlete, ['ageGradedTime' => $ageGradedTime]);
            }
        }
        
        // Sort by age-graded time
        usort($ageGradedResults, function($a, $b) {
            return $a['ageGradedTime'] <=> $b['ageGradedTime'];
        });
        
        return $ageGradedResults;
    }
}
```

## Development

### Run Tests
```bash
composer test
```

### Run Example
```bash
php age-grade-tables.php
```

## License

MIT

## Contributing

Contributions are welcome! Please open an issue or submit a pull request. 