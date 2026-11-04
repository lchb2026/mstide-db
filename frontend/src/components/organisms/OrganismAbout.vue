<script setup lang="ts">
// Core
import { ref } from 'vue';

// Modules
import {
  mdiMolecule,
  mdiAtom,
  mdiSelectCompare,
  mdiChevronLeft,
  mdiChevronRight,
} from '@mdi/js';

// Data
const instructionKey = ref<number>(0);
</script>

<template>
  <v-main>
    <v-container class="pa-6 pa-md-12 v-col-auto" fluid>
      <h2 class="font-weight-bold text-justify">About msTide 2.0</h2>
      <v-row justify="center">
        <v-col cols="12" lg="6" order="1" order-lg="1">
          <p class="mb-2 text-justify">
            msTide 2.0 is an open-access database of negative-ion electrospray tandem mass 
            spectrometry (ESI-MS/MS) data for biologically and therapeutically relevant 
            di- and trinucleotides and their synthetic analogs. It extends the original 
            database at <a
            href="https://mstide-db1.com/"
            target="_blank"
            >mstide-db1.com</a
            >, which contains fragmentation spectra of over 150 mononucleotides and their derivatives.
          </p>

          <p class="mb-2 text-justify">
            The database includes structurally diverse nucleotide analogs, including mRNA 
            cap analogs, nucleotide cofactors, and compounds differing in nucleobase composition,
            oligophosphate chain length, and modifications within the phosphate, ribose, 
            or nucleobase moieties.
          </p>

          <p class="mb-2 text-justify">
            <b>Experimental data and fragment annotations</b>
          </p>

          <p class="mb-2 text-justify">
            MS/MS spectra were acquired using electrospray ionization in negative-ion mode 
            and collision-induced dissociation (CID). Spectra of singly and doubly charged 
            precursor ions were analyzed separately using compound-specific collision energies 
            (CE) and declustering potentials (DP).
          </p>

          <p class="mb-2 text-justify">
            Each database entry provides the compound structure, name and abbreviation, 
            experimental conditions, an annotated MS/MS spectrum, and a peak list with 
            normalized relative intensities. Proposed chemical structures are provided 
            for selected product ions and were manually reviewed by the authors.
          </p>

          <p class="mb-2 text-justify">
            The displayed spectra were processed using a relative intensity threshold 
            of 1% of the base peak. Consequently, the absence of a signal from the 
            processed spectrum does not necessarily indicate its complete absence 
            from the original measurement.
          </p>

          <p class="mb-2 text-justify">
            <b>Search and comparison tools</b>
          </p>

          <p class="mb-2 text-justify">
            Users can browse and search compounds by name, molecular formula, mass-to-charge 
            ratio (m/z), structural features, and modification type. The database also supports 
            the examination of individual product ions, including their proposed structures 
            (available in the <a
            href="https://www.mstide-db2.com/fragment/list"
            target="_blank"
            >fragment reference section</a
            >) and occurrence across different compounds. 
            Additionally, users can directly compare experimental MS/MS spectra of different 
            compounds to identify similarities and differences in their fragmentation patterns.
          </p>

          <p class="mb-2 text-justify">
            <b>Applications</b>
          </p>

          <p class="mb-2 text-justify">
            msTide 2.0 is intended to support interpretation of nucleotide fragmentation pathways, 
            structural characterization of synthetic nucleotide analogs, investigation of nucleotide 
            modifications, and development of mass spectrometry-based analytical methods.
          </p>

          <p class="mb-2 text-justify">
            The experimentally acquired reference spectra may also support the development of methods 
            for nucleotide metabolite identification and targeted analysis of selected nucleotides. 
            The resource is freely accessible without registration.
          </p>

          <p class="mb-2 text-justify">
            <b>Data and software availability</b>
          </p>

          <p class="mb-2 text-justify">
            The experimental dataset, including original instrument data, processed MS/MS spectra, 
            and associated metadata, is publicly available on <a
            href="https://doi.org/10.5281/zenodo.23061295"
            target="_blank"
            >Zenodo</a
            >.
          </p>

          <p class="mb-2 text-justify">
            The source code of msTide 2.0 is available on <a
            href="https://github.com/lchb2026/mstide-db"
            target="_blank"
            >GitHub</a
            >, with an archived software 
            release deposited on <a
            href="https://doi.org/10.5281/zenodo.23099072"
            target="_blank"
            >Zenodo</a
            >.
          </p>

          <p class="mb-2 text-justify">
            <b>Associated publication</b>
          </p>

          <p class="mb-2 text-justify">
            Zhilyaev D., Strzelecka D., Golojuch S., Pandiurin A., Jemielity J., Kowalska J. 
            Analysis of biologically and therapeutically relevant di- and trinucleotide analogs 
            by tandem mass spectrometry.
          </p>
        </v-col>

        <v-col cols="12" lg="5" offset-lg="1" order-lg="2" order="2" class="border-sm rounded-lg">
          <h3 class="font-weight-bold">Instruction</h3>
          <v-window v-model="instructionKey" :show-arrows="false">
            <v-window-item :value="0">
              <v-card class="px-4 py-4" elevation="0">
                <p class="mb-2"><v-icon :icon="mdiMolecule" />&nbsp;Compounds</p>
                <p class="font-weight-bold">Search Methods for Compounds</p>
                <p>
                  <v-list lines="two">
                    <v-list-item>
                      <v-list-item-title>1. The 1- and 2- charge filters</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Select the corresponding buttons (1 charge (-), 2 charges (2-)) to display
                        all compounds with tandem mass spectra for singly charged (-) or doubly
                        charged (2-) ions.
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>2. Compound Name search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Search by IUPAC names, trivial names, or abbreviations. Partial names are
                        also accepted.
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>3. Molecular Formula search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Retrieve a list of compounds by specifying the exact number of each atom in
                        the molecular formula.
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>4. ID search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Search using a unique catalog ID formatted as X.Y.Z (e.g., 388.2.1). X –
                        molecular weight, Y – index of compounds with the same molecular weight, Z –
                        charge state of the parent ion.
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>5. Canonical SMILES search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Retrieve compounds that contain a specific scaffold structure. Input should
                        be in canonical SMILES format (full or partial), which can be generated by
                        any chemical drawing software.</v-list-item-title
                      >
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>6. m/z search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Generate a list of compounds that match the specified m/z value(s).
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                      <v-list-item-title>7. Modification-Type Filter</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Narrow down the list of compounds by selecting specific types of
                        base/ribose/phosphate modifications.
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </p>
              </v-card>
            </v-window-item>
            <v-window-item :value="1">
              <v-card class="px-4 py-4" elevation="0">
                <p class="mb-2"><v-icon :icon="mdiAtom" />&nbsp;Fragments</p>
                <p class="font-weight-bold">Search Methods for Fragments</p>
                <p>
                  <v-list lines="two">
                    <v-list-item>
                      <v-list-item-title>1. Fragment Abbreviation search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Search by abbreviations.
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title>2. Canonical SMILES search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Retrieve fragments that contain a specific scaffold structure. Input should
                        be in canonical SMILES format.
                      </v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title>3. m/z search</v-list-item-title>
                      <v-list-item-title class="text-medium-emphasis text-pre-wrap text-justify"
                        >Generate a list of fragment ions matching the provided m/z value.
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </p>
              </v-card>
            </v-window-item>
            <v-window-item :value="2">
              <v-card class="px-4 py-4" elevation="0">
                <p class="mb-2"><v-icon :icon="mdiSelectCompare" />&nbsp;Compare</p>
                <p class="font-weight-bold">Spectra comparison and Match Factor calculation</p>
                <p class="pt-5 text-justify">
                  The resulting spectra can also be compared to obtain additional insight. To
                  activate the comparison function, check the boxes in the upper left corner of two
                  or more compound cards. Once the compounds have been selected, their superimposed
                  spectra will be displayed together with the corresponding match factor. This
                  value, ranging from 0 to 999, reflects the degree of similarity between the
                  normalized mass spectra.
                </p>
              </v-card>
            </v-window-item>
          </v-window>

          <div class="d-flex align-center justify-space-between">
            <v-btn
              icon
              aria-label="Previous instruction"
              @click="instructionKey = (instructionKey + 2) % 3"
            >
              <v-icon :icon="mdiChevronLeft" />
            </v-btn>

            <span class="text-body-2 text-medium-emphasis" aria-live="polite">
              {{ instructionKey + 1 }} / 3
            </span>

            <v-btn
              icon
              aria-label="Next instruction"
              @click="instructionKey = (instructionKey + 1) % 3"
            >
              <v-icon :icon="mdiChevronRight" />
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
