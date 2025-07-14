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
console.log(factor); // 0.98
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