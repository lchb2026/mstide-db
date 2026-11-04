import type { MassTable } from '@/interfaces/MassTable';
import type { Fragment } from '@/interfaces/Fragment';
import type { CompoundType } from '@/interfaces/CompoundType';

export interface Compound {
  id?: number;
  abbr?: string;
  name?: string;
  molecularFormula?: string;
  canonicalSmiles?: string;
  compoundId?: string;
  instrument?: string;
  scanType?: string;
  ionization?: string;
  declusteringPotential?: string;
  collisionEnergy?: string;
  massTable?: MassTable[];
  fragments: Fragment[];
  picture?: string;
  sdf?: string;
  types: CompoundType[];
  typesText: string;
}
