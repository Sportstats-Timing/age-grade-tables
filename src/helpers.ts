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