import { TableName, Format, AgeGradeCompactEntry, AgeGradeObjectEntry } from './types';
import { ageGradeTable_2025_ironman } from './tables/2025_ironman';
import { ageGradeTable_2025_ironman703 } from './tables/2025_ironman703';

// Main exports for the age-grade-tables package
export * from './types';
export * from './helpers';

/**
 * Get age grade table by name and format
 * 
 * Retrieves age grading tables for triathlon events from memory. Age grading tables
 * provide performance factors for different age groups and genders, allowing comparison
 * of performance across different demographics.
 * 
 * @param name - The table name to retrieve. Currently supports:
 *   - '2025_ironman': Full Ironman distance (2.4mi swim, 112mi bike, 26.2mi run)
 *   - '2025_ironman703': Ironman 70.3 distance (1.2mi swim, 56mi bike, 13.1mi run)
 * @param format - The output format for the table data:
 *   - 'array': Returns compact array format `[gender, start_age, end_age, factor]`
 *   - 'json': Returns object format `{gender, start, end, factor}`
 * @returns The age grade table in the specified format
 * 
 * @throws {Error} When an unknown table name is provided
 * @throws {Error} When an unknown format is specified
 * 
 * @example
 * ```typescript
 * // Get Ironman table in array format (default)
 * const ironmanArray = getAgeGradeTable('2025_ironman');
 * // Returns: [["M", 0, 19, 1.000], ["M", 20, 24, 0.995], ...]
 * 
 * // Get Ironman 70.3 table in JSON format
 * const ironman703Json = getAgeGradeTable('2025_ironman703', 'json');
 * // Returns: [{ gender: "M", start: 0, end: 19, factor: 1.000 }, ...]
 * 
 * // Find age grade factor for a 35-year-old male in Ironman
 * const ironmanTable = getAgeGradeTable('2025_ironman', 'array') as AgeGradeCompactEntry[];
 * const male35Entry = ironmanTable.find(entry => 
 *   entry[0] === 'M' && entry[1] <= 35 && entry[2] >= 35
 * );
 * const factor = male35Entry?.[3]; // 0.980
 * ```
 * 
 * @example
 * ```typescript
 * // Calculate age-graded performance
 * const table = getAgeGradeTable('2025_ironman', 'json') as AgeGradeObjectEntry[];
 * const age = 35;
 * const gender = 'M';
 * const finishTime = 9 * 3600 + 30 * 60; // 9:30:00 in seconds
 * 
 * const entry = table.find(e => 
 *   e.gender === gender && e.start <= age && e.end >= age
 * );
 * 
 * if (entry) {
 *   const ageGradedTime = finishTime / entry.factor;
 *   console.log(`Age-graded time: ${ageGradedTime} seconds`);
 * }
 * ```
 */
export function getAgeGradeTable(name: TableName, format: Format = 'array'): AgeGradeCompactEntry[] | AgeGradeObjectEntry[] {
  let table: AgeGradeCompactEntry[];
  
  // Get the appropriate table from memory
  switch (name) {
    case '2025_ironman':
      table = ageGradeTable_2025_ironman;
      break;
    case '2025_ironman703':
      table = ageGradeTable_2025_ironman703;
      break;
    default:
      throw new Error(`Unknown table name: ${name}`);
  }
  
  // Return in requested format
  if (format === 'array') {
    return table;
  } else if (format === 'json') {
    // Convert compact array format to object format
    return table.map(([gender, start, end, factor]): AgeGradeObjectEntry => ({
      gender,
      start,
      end,
      factor
    }));
  } else {
    throw new Error(`Unknown format: ${format}`);
  }
} 