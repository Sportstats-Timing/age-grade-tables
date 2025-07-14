import { AgeGradeCompactEntry } from './types';

/**
 * Get the age grade factor for a given gender and age from a compact age-grade table.
 *
 * @param table - The compact age-grade table (array of [gender, start, end, factor])
 * @param gender - The gender to match (e.g., 'M' or 'F')
 * @param age - The age to match (inclusive between start and end)
 * @returns The matching factor if found, otherwise null
 *
 * @example
 * ```typescript
 * import { getFactorByAgeAndGender } from './helpers';
 * import { ageGradeTable_2025_ironman } from './tables/2025_ironman';
 *
 * const factor = getFactorByAgeAndGender(ageGradeTable_2025_ironman, 'M', 37);
 * // factor will be 0.98
 * ```
 */
export function getFactorByAgeAndGender(
  table: AgeGradeCompactEntry[],
  gender: string,
  age: number
): number | null {
  const entry = table.find(([g, start, end]) => g === gender && age >= start && age <= end);
  return entry ? entry[3] : null;
}

/**
 * Creates a fast lookup map for bulk age grade factor retrieval
 * @param table - Age grade table in array format
 * @returns Map with key format: "gender_age" -> factor
 */
export function createAgeGradeLookup(table: AgeGradeCompactEntry[]): Map<string, number> {
  const lookup = new Map<string, number>();
  
  for (const [gender, startAge, endAge, factor] of table) {
    for (let age = startAge; age <= endAge; age++) {
      lookup.set(`${gender}_${age}`, factor);
    }
  }
  
  return lookup;
}

/**
 * Get age grade factor for a single athlete using a pre-built lookup map
 * @param lookup - Pre-built lookup map from createAgeGradeLookup
 * @param gender - Athlete gender ('M' or 'F')
 * @param age - Athlete age
 * @returns Age grade factor or null if not found
 */
export function getFactorFromLookup(lookup: Map<string, number>, gender: string, age: number): number | null {
  return lookup.get(`${gender}_${age}`) || null;
}

/**
 * Process multiple athletes efficiently using a pre-built lookup map
 * @param lookup - Pre-built lookup map from createAgeGradeLookup
 * @param athletes - Array of athletes with age and gender
 * @returns Array of athletes with their age grade factors
 */
export function processAthletesBulk(
  lookup: Map<string, number>, 
  athletes: Array<{ age: number; gender: string; [key: string]: any }>
): Array<{ age: number; gender: string; factor: number | null; [key: string]: any }> {
  return athletes.map(athlete => ({
    ...athlete,
    factor: getFactorFromLookup(lookup, athlete.gender, athlete.age)
  }));
} 