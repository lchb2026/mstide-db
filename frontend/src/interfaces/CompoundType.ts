import type { CompoundTypeGroup } from '@/interfaces/CompoundTypeGroup';

export interface CompoundType {
  id: number;
  name: string;
  group?: CompoundTypeGroup | null;
}
