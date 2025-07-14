# Age Grade Tables

A TypeScript package providing age grading tables for races like Ironman and Ironman 70.3 in a compact, efficient format. Includes helpers for retrieving age grade factors by age and gender.

## Features

- **Compact Data Format**: Efficient 5-year age group tables for both genders
- **TypeScript Support**: Full type definitions and IntelliSense
- **Simple API**: Retrieve tables and factors with a single function call
- **Zero Dependencies**: Lightweight and fast

## Installation

```bash
npm install age-grade-tables
```

## Usage

### Get a Table in Array or JSON Format

```typescript
import { getAgeGradeTable } from 'age-grade-tables';

// Get the 2025 Ironman 70.3 table in compact array format
const arrayFormat = getAgeGradeTable('2025_ironman703', 'array');
console.log(arrayFormat);
// Output: [ ["M", 0, 19, 1.000], ["M", 20, 24, 0.995], ... ]

// Get the same table in object (JSON) format
const jsonFormat = getAgeGradeTable('2025_ironman703', 'json');
console.log(jsonFormat);
// Output: [ { gender: "M", start: 0, end: 19, factor: 1.000 }, ... ]
```

### Get a Factor by Age and Gender

```typescript
import { getAgeGradeTable, getFactorByAgeAndGender } from 'age-grade-tables';

const table = getAgeGradeTable('2025_ironman', 'array');
const factor = getFactorByAgeAndGender(table, 'M', 37);
console.log(factor); // 0.9895
```

### Bulk Processing for Large Datasets (40,000+ athletes)

For processing large numbers of athletes efficiently:

```typescript
import { 
  getAgeGradeTable, 
  createAgeGradeLookup, 
  processAthletesBulk 
} from 'age-grade-tables';

// Setup: Create fast lookup table (do once)
const table = getAgeGradeTable('2025_ironman', 'array');
const lookup = createAgeGradeLookup(table);

// Process 40,000 athletes efficiently
const athletes = [
  { id: 1, age: 35, gender: 'M', finishTime: 32400 },
  { id: 2, age: 42, gender: 'F', finishTime: 34200 },
  // ... 40,000 athletes
];

const results = processAthletesBulk(lookup, athletes);
// Returns: [{ id: 1, age: 35, gender: 'M', finishTime: 32400, factor: 0.9895 }, ...]

// Calculate age-graded times
const ageGradedResults = results.map(athlete => ({
  ...athlete,
  ageGradedTime: athlete.factor ? athlete.finishTime / athlete.factor : null
}));
```

## API Reference

### Tables
- `getAgeGradeTable(name: TableName, format: Format = 'array')`
  - `name`: `'2025_ironman' | '2025_ironman703'`
  - `format`: `'array'` (default) or `'json'`
  - Returns: Age grade table in the requested format

### Helpers
- `getFactorByAgeAndGender(table, gender, age)`
  - `table`: Age grade table (array format)
  - `gender`: `'M'` or `'F'`
  - `age`: number (athlete's age)
  - Returns: The factor for the matching age/gender, or `null` if not found

### Bulk Processing (for large datasets)
- `createAgeGradeLookup(table)`
  - `table`: Age grade table (array format)
  - Returns: Fast lookup Map for O(1) factor retrieval
- `processAthletesBulk(lookup, athletes)`
  - `lookup`: Pre-built lookup Map
  - `athletes`: Array of athlete objects with `age` and `gender` properties
  - Returns: Array of athletes with their age grade factors added
- `getFactorFromLookup(lookup, gender, age)`
  - `lookup`: Pre-built lookup Map
  - `gender`: `'M'` or `'F'`
  - `age`: number (athlete's age)
  - Returns: The factor for the matching age/gender, or `null` if not found

### Types
- `AgeGradeCompactEntry`: `[string, number, number, number]` — `[gender, start, end, factor]`
- `AgeGradeObjectEntry`: `{ gender: string; start: number; end: number; factor: number }`
- `TableName`: `'2025_ironman' | '2025_ironman703'`
- `Format`: `'array' | 'json'`

## Data Format

Tables are stored as compact arrays:

```typescript
type AgeGradeCompactEntry = [string, number, number, number];
// [gender, start_age, end_age, factor]
```

Example:
```typescript
["M", 35, 39, 0.980] // Males age 35-39, factor 0.980
```

## Development

### Build
```bash
npm run build
```

### Test
```bash
npm test
```

## License

MIT

## Contributing

Contributions are welcome! Please open an issue or submit a pull request. 