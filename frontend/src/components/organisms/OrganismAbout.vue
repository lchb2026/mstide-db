<script setup lang="ts">
// Core
import { ref } from 'vue';

// Modules
import { mdiMolecule, mdiAtom, mdiSelectCompare } from '@mdi/js';

// Data
const instructionKey = ref<number>(0);
</script>

<template>
  <v-main>
    <v-container class="pa-6 pa-md-12 v-col-auto" fluid>
      <h2 class="font-weight-bold text-justify">About MSTIDE Database 2.0</h2>

      <p class="mt-4 mb-4 text-body-1 text-justify">
        The MSTIDE Database 2.0 is an extension of a previously developed resource from our
        laboratory, which contains data on more than 150 mononucleotides (<a
          href="http://www.mstide-db.com/"
          target="_blank"
          >mstide-db.com</a
        >). It serves as a repository of negative-ion mode tandem mass spectrometry data for di- and
        trinucleotides, encompassing variations in nucleobase composition, oligophosphate chain
        length (from mono- to pentaphosphate), and additional modifications on the phosphate,
        ribose, or nucleobase moieties.
      </p>

      <v-row justify="center">
        <v-col cols="12" lg="6" order="2" order-lg="1">
          <p class="mb-2 text-justify">
            This database was established to complement a research paper focused on the
            fragmentation mechanisms of modified nucleotides:
          </p>
          <p class="mb-2 text-justify">
            <b>Zhilyaev D, Strzelecka D, Golojuch S, Pandiurin A, Jemielity J, Kowalska J*</b>
            Analysis of biologically and therapeutically relevant di- and trinucleotide analogues by
            tandem mass spectrometry.
          </p>

          <p class="mb-2 text-justify">
            <i
              >*Contact corresponding author: Joanna.Kowalska AT
              <a href="https://www.fuw.edu.pl/" target="_blank">fuw.edu.pl</a></i
            >
          </p>

          <p class="mt-6 text-justify">
            The MSTIDE Database 2.0 is a repository of negative ion tandem mass spectrometry data
            for natural nucleotides and their synthetic derivatives. These compounds included
            numerous biologically and pharmaceutically significant dinucleotides, varying in the
            type of nitrogenous base, the length of the oligophosphate chain (from mono- to
            pentaphosphate), and the presence of additional modifications in the phosphate, ribose,
            or nitrogenous baseresidues. Trinucleotides with various modifications including mRNA
            cap analogs were represented. For all of compounds, both single and double charged
            pseudomolecular ions were observed in the MS spectra, and the fragmentation behavior of
            each precursor ion was analyzed separately. For every entry, users can access the parent
            ion’s chemical structure, IUPAC name and abbreviation, detailed measurement conditions,
            the recorded MS/MS spectrum, a numerical list of detected peaks with normalized
            intensities, and a set of proposed chemical structures for identified fragment ions. All
            fragments were manually assigned and evaluated by the authors of the referenced study.
            MS/MS spectra were obtained using carefully optimized collision energies and
            depolarization potentials to ensure comprehensive fragmentation data for each compound.
            Throughout the study, soft ionization was performed using electrospray ionization (ESI),
            and fragmentation was achieved through collision-induced dissociation (CID). Only peaks
            with intensities above 1% of the base peak were included in the final spectra extracted
            from the original raw data.
          </p>

          <p class="mt-4 text-justify">
            The database provides multiple search functionalities, including monoisotopic mass
            search, single or multiple <i>m/z</i> peak searches, substructure and structural
            similarity searches, as well as filtering of compounds based on modification(s) present
            in the precursor.
          </p>

          <p class="mt-4 text-justify">
            The database is intended to facilitate detailed analysis of nucleotide fragmentation
            patterns, enabling easier identification of metabolites, synthetic products, fragment
            ions, and nucleotide modifications. The information it contains can support the
            development of mass spectrometry–based methods for diagnosing nucleotide-related
            diseases, identifying nucleotide metabolites, and performing quantitative analyses of
            selected nucleotides. It can also be applied to the rapid, low-material identification
            of nucleotide-derived synthetic products. All data in the library is available free of
            charge, no registration required.
          </p>
        </v-col>

        <v-col cols="12" lg="5" offset-lg="1" order-lg="2" class="border-sm rounded-lg">
          <h3 class="font-weight-bold">Instruction</h3>
          <v-window v-model="instructionKey" show-arrows="hover">
            <v-window-item :key="0">
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
            <v-window-item :key="1">
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
            <v-window-item :key="2">
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
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
