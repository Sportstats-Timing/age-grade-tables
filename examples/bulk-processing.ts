import { 
  getAgeGradeTable, 
  createAgeGradeLookup, 
  processAthletesBulk,
  getFactorFromLookup 
} from '../src/index';

// Example: Processing 40,000 athletes efficiently

// 1. SETUP: Load table and create fast lookup (do this once)
console.log('Setting up fast lookup...');
const ironmanTable = getAgeGradeTable('2025_ironman', 'array') as any[];
const lookup = createAgeGradeLookup(ironmanTable);

// 2. EXAMPLE: Generate 40,000 sample athletes
function generateSampleAthletes(count: number) {
  const athletes: Array<{ id: number; name: string; age: number; gender: 'M' | 'F'; finishTime: number }> = [];
  const genders = ['M', 'F'];
  
  for (let i = 0; i < count; i++) {
    athletes.push({
      id: i + 1,
      name: `Athlete ${i + 1}`,
      age: Math.floor(Math.random() * 72) + 18, // Random age 18-89
      gender: genders[Math.floor(Math.random() * 2)] as 'M' | 'F',
      finishTime: Math.floor(Math.random() * 7200) + 28800, // Random time 8-10 hours
    });
  }
  
  return athletes;
}

// 3. METHOD 1: Bulk processing (fastest for large datasets)
console.log('Processing athletes in bulk...');
const athletes = generateSampleAthletes(40000);

console.time('Bulk processing 40k athletes');
const processedAthletes = processAthletesBulk(lookup, athletes);
console.timeEnd('Bulk processing 40k athletes');

// 4. METHOD 2: Individual lookups (good for smaller batches or real-time)
console.log('Processing individual athletes...');
console.time('Individual processing 1k athletes');
const sampleAthletes = athletes.slice(0, 1000);
const individualResults = sampleAthletes.map(athlete => ({
  ...athlete,
  factor: getFactorFromLookup(lookup, athlete.gender, athlete.age)
}));
console.timeEnd('Individual processing 1k athletes');

// 5. EXAMPLE: Calculate age-graded times
console.log('Calculating age-graded times...');
const ageGradedResults = processedAthletes.slice(0, 5).map(athlete => {
  if (athlete.factor) {
    const ageGradedTime = athlete.finishTime / athlete.factor;
    return {
      ...athlete,
      ageGradedTime: Math.round(ageGradedTime),
      improvement: Math.round((athlete.finishTime - ageGradedTime) / 60) // minutes
    };
  }
  return athlete;
});

// 6. OUTPUT: Show sample results
console.log('\nSample Results:');
console.table(ageGradedResults.map(a => ({
  ID: a.id,
  Age: a.age,
  Gender: a.gender,
  'Finish Time (hrs)': (a.finishTime / 3600).toFixed(2),
  'Age Grade Factor': a.factor?.toFixed(4),
  'Age Graded Time (hrs)': a.ageGradedTime ? (a.ageGradedTime / 3600).toFixed(2) : 'N/A',
  'Improvement (mins)': a.improvement || 'N/A'
})));

// 7. STATISTICS
const validFactors = processedAthletes.filter(a => a.factor !== null);
console.log(`\nStatistics:`);
console.log(`- Total athletes processed: ${processedAthletes.length}`);
console.log(`- Athletes with valid factors: ${validFactors.length}`);
console.log(`- Average age grade factor: ${(validFactors.reduce((sum, a) => sum + (a.factor || 0), 0) / validFactors.length).toFixed(4)}`);

// 8. PERFORMANCE COMPARISON
console.log('\nPerformance Comparison:');
console.log('- Bulk processing: ~1-5ms for 40k athletes');
console.log('- Individual lookups: ~0.1-0.5ms per athlete');
console.log('- Memory usage: ~2-5MB for lookup table');

// 9. REAL-WORLD USAGE EXAMPLE
function processRaceResults(athletes: Array<{ id: number; age: number; gender: string; finishTime: number }>) {
  // Setup (do once per race)
  const table = getAgeGradeTable('2025_ironman', 'array') as any[];
  const lookup = createAgeGradeLookup(table);
  
  // Process all athletes
  const results = processAthletesBulk(lookup, athletes);
  
  // Calculate age-graded times and rankings
  return results
    .filter(a => a.factor !== null)
    .map(a => ({
      ...a,
      ageGradedTime: a.finishTime / (a.factor || 1)
    }))
    .sort((a, b) => a.ageGradedTime - b.ageGradedTime);
}

export { processRaceResults }; 