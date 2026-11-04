import type { Compound } from '@/interfaces/Compound';

export interface Fragment {
  id?: number;
  name?: string;
  canonicalSmiles?: string;
  molecularMass?: number;
  compounds: Compound[];
  picture?: string;
}
