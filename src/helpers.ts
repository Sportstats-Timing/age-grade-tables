import { AgeGradeCompactEntry, AgeGradeObjectEntry } from './types';

/**
 * Get age grade factor for a specific age and gender
 * @param table - Age grade table in array format
 * @param gender - Athlete gender ('M' or 'F')
 * @param age - Athlete age
 * @returns Age grade factor or null if not found
 */
export function getFactorByAgeAndGender(
  table: AgeGradeCompactEntry[] | AgeGradeObjectEntry[], 
  gender: string, 
  age: number
): number | null {
  // Handle both array and object formats
  if (table.length > 0 && Array.isArray(table[0])) {
    // Array format: [gender, start, end, factor]
    const arrayTable = table as AgeGradeCompactEntry[];
    const entry = arrayTable.find(([g, start, end]) => 
      g === gender && age >= start && age <= end
    );
    return entry ? entry[3] : null;
  } else {
    // Object format: {gender, start, end, factor}
    const objectTable = table as AgeGradeObjectEntry[];
    const entry = objectTable.find(({ gender: g, start, end }) => 
      g === gender && age >= start && age <= end
    );
    return entry ? entry.factor : null;
  }
}

/**
 * Creates a fast lookup map for bulk age grade factor retrieval
 * @param table - Age grade table in array or object format
 * @returns Map with key format: "gender_age" -> factor
 */
export function createAgeGradeLookup(table: AgeGradeCompactEntry[] | AgeGradeObjectEntry[]): Map<string, number> {
  const lookup = new Map<string, number>();
  
  // Handle both array and object formats
  if (table.length > 0 && Array.isArray(table[0])) {
    // Array format
    const arrayTable = table as AgeGradeCompactEntry[];
    for (const [gender, startAge, endAge, factor] of arrayTable) {
      for (let age = startAge; age <= endAge; age++) {
        lookup.set(`${gender}_${age}`, factor);
      }
    }
  } else {
    // Object format
    const objectTable = table as AgeGradeObjectEntry[];
    for (const { gender, start: startAge, end: endAge, factor } of objectTable) {
      for (let age = startAge; age <= endAge; age++) {
        lookup.set(`${gender}_${age}`, factor);
      }
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