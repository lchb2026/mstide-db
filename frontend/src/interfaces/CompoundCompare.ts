import type { MassTable } from '@/interfaces/MassTable';

export interface CompoundCompare {
  id: number;
  abbr: string | null;
  name: string | null;
  massTable: MassTable[];
  picture: string | null;
}
