import { getAgeGradeTable } from '../src';

console.log('--- 2025 Ironman 70.3 Table (Array Format) ---');
const arrayFormat = getAgeGradeTable('2025_ironman703', 'array');
console.log(arrayFormat);

console.log('\n--- 2025 Ironman 70.3 Table (JSON/Object Format) ---');
const jsonFormat = getAgeGradeTable('2025_ironman703', 'json');
console.log(jsonFormat); 