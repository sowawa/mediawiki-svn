#!/usr/bin/python
# -*- coding: utf-8 -*-

import timeit

setup_stmt = 'import pyhash; hasher = pyhash.murmur2a_32()'
t = timeit.Timer('''
text = """{{redirect-distinguish|IUPAC|International Union of Painters and Allied Trades|IUPAT}}\
[[Image:IUPAC.svg|thumb|upright|alt=A black and white image of an old fashioned scale with two spheres below it. Below the spheres is some chemistry apparati.|150px|IUPAC logo]]
The \'\'\'International Union of Pure and Applied Chemistry\'\'\' (\'\'\'IUPAC\'\'\', pronounced {{IPA-en|ˈaɪjuːpæk|}} {{respell|EYE|ew-pak}} or {{IPA-en|ˈjuːpæk|}} {{respell|EW|pak}}) is an international federation of [[National Adhering Organizations]] that represents chemists in individual countries. It is a member of the [[International Council for Science]] (ICSU).<ref name="nao">{{cite web|url=http://www.iupac.org/nao |title=IUPAC National Adhering Organizations |publisher=Iupac.org |date=2011-06-02 |accessdate=2011-06-08}}</ref> The international headquarters of IUPAC is located in [[Zürich]], [[Switzerland]]. The administrative office, known as the "IUPAC Secretariat", is located in [[Research Triangle Park]], [[North Carolina]], [[United States]]. This administrative office is headed by the IUPAC executive director.<ref>{{cite web|url=http://old.iupac.org/symposia/conferences/ga09/Council_Agenda_Book_2009.pdf|title=IUPAC Council Agenda Book 2009|publisher=IUPAC|accessdate=2010-04-17|year=2009}}</ref>

IUPAC was established in 1919 as the successor of the International Congress of Applied Chemistry for the advancement of [[chemistry]]. Its members, the National Adhering Organizations, can be national [[List of chemistry societies|chemistry societies]], national [[Academy of Sciences|academies of sciences]], or other bodies representing chemists. There are fifty-four National Adhering Organizations and three Associate National Adhering Organizations.<ref name="nao"/> IUPAC\'s Inter-divisional Committee on Nomenclature and Symbols ([[International Union of Pure and Applied Chemistry nomenclature|IUPAC nomenclature]]) is the recognized world authority in developing standards for the naming of the [[chemical elements]] and [[chemical compounds|compounds]]. Since its creation, IUPAC has been run by many different committees with different responsibilities.<ref name="governing committees">[http://www.iupac.org/Organization/Committees IUPAC Committees list] retrieved 15 April 2010</ref> These committees all run different projects which include standardizing [[nomenclature]],<ref name="Interdivisional Committee on Terminology">[http://www.iupac.org/web/ins/027 Interdivisional Committee on Terminology web page] retrieved 15 April 2010</ref> finding ways to bring [[chemistry]] to the world,<ref name="CHEMRAWN activities">[http://www.iupac.org/news/archives/2007/44th_council/Item_12-2_2007.pdf Chemdrawn] retrieved 15 April 2010</ref> and publishing works.<ref name="Pure and Applied Chemistry Editorial Advisory Board">[http://www.iupac.org/web/ins/030 Pure and Applied Chemistry Editorial Advisory Board web page] retrieved 15 April 2010</ref><ref name="Project Committee">{{cite web|url=http://www.iupac.org/web/ins/013 |title=Project Committee web page |publisher=Iupac.org |date=2011-06-02 |accessdate=2011-06-08}}</ref><ref name="Evaluation Committee">[http://www.iupac.org/web/ins/014 Evaluation Committee page] retrieved 15 April 2010</ref>

IUPAC is best known for its works standardizing nomenclature in chemistry and other fields of science, but IUPAC has publications in many fields including chemistry, biology and physics.<ref name="History of IUPAC"/> Some important work IUPAC has done in these fields includes standardizing [[nucleotide]] base sequence code names; publishing books for environmental scientists, chemists, and physicists; and leading the way in improving education in science.<ref name="History of IUPAC"/><ref name="IYC">[http://www.chemistry2011.org/about-iyc/introduction IYC: Introduction.] July 9, 2009. Retrieved on February 17, 2010. retrieved 15 April 2010</ref>

==Creation and history==
[[Image:Frkekulé.jpg|thumb|upright|alt=A black and white image of a bald man in a dark outfit, with a bushy white beard and moustache|Friedrich August Kekulé von Stradonitz]]The need for an international standard for chemistry was first addressed in 1860 by a committee headed by German scientist [[Friedrich August Kekulé von Stradonitz]]. This committee was the first international conference to create an international naming system for [[organic compounds]].<ref name="History of IUPAC">{{Cite book|last= Fennel |first= R.W. |title= History of IUPAC, 1919-1987 |publisher= Blackwell Science |year= 1994 |isbn= 0-86542-8786(94)}}</ref> The ideas that were formulated in that conference evolved into the official [[IUPAC nomenclature of organic chemistry]].<ref name="History of IUPAC"/> The IUPAC stands as a legacy of this meeting, making it one of the most important historical international collaborations of [[List of chemistry societies|chemistry societies]].<ref name="History of IUPAC"/>

The ideas of committee of 1860 were further addressed by the first international proposal in 1892. The rules established by this proposal are known as the [[Geneva Rules]]. The Geneva Rules first officially standardized some [[Organic Chemistry]] names and rules. The largest contribution that the Geneva Rules made to Organic Chemistry is the establishment of Organic [[Root (linguistics)|root names]].<ref name="first proposal committee">[http://www.patentstorm.us/patents/4747059/description.html Beginnings of standardization] retrieved 15 April 2010</ref> The conference in [[Geneva]] was held by a chemistry society that also was precursor to IUPAC called The International Union of Chemistry.<ref name="Chemistry The Central Science">{{Cite book|last= Brown |first= Theodore L. |coauthors= H. Eugene LeMay Jr, Bruce E Bursten |title= Chemistry The Central Science Tenth Edition |publisher= Pearson Books |year= 2006 |isbn= 0-13-109686-9}}</ref>

The work from the first international proposal was continued by the Commission for the Reform of [[Nomenclature]] in [[Organic Chemistry]]. The Commission for the Reform of Nomenclature was headed by The International Union of Chemistry.<ref name="first proposal committee"/> The International Union of Chemistry continued to work on the naming of Organic compounds until the advent of [[World War I]], when international communications got difficult.<ref name="History of IUPAC"/>

After World War I, discussion ensued about the formation of a new permanent international chemistry society. At this time, the basic [[nomenclature]] of [[Organic compounds]] was established. However, a new organization needed to be established in order to continue work on the standardizing of chemistry.<ref name="History of IUPAC"/> This prompted the creation of the International Union of Pure and Applied Chemistry (IUPAC) in 1919. Since this time, IUPAC has been the official organization held with the responsibility of updating and maintaining official [[organic nomenclature]].<ref name="Chemistry The Central Science"/> One notable country excluded from the early IUPAC was [[Germany]]. Germany\'s exclusion was a result of prejudice towards Germans by the allied powers after [[World War I]]<ref name="Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts">{{Cite book|last= Kaderas |first= Brigitte |title= Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts|language=German |publisher= Franz Steiner Verlag |year= 2002 |isbn= 3-515-08111-9}}</ref> [[Germany]] was finally admitted into IUPAC during 1929. However, [[Nazi Germany]] was removed from IUPAC during [[World War II]]

During World War II, IUPAC was affiliated with the [[allied powers]], but had little involvement during the war effort itself. After the war, West [[Germany]] was allowed back into IUPAC.<ref name="Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts"/> Since World War II, IUPAC has been focused on standardizing nomenclature and methods in science without interruption.

==Committees and governance==
IUPAC is governed by several [[committees]] that all have different responsibilities. The committees are as follows: Bureau, CHEMRAWN (Chem Research Applied to World Needs) Committee, Committee on Chemistry Education, Committee on Chemistry and Industry, Committee on Printed and Electronic Publications, Evaluation Committee, Executive Committee, Finance Committee, Interdivisional Committee on Terminology, Nomenclature and Symbols, Project Committee, Pure and Applied Chemistry Editorial Advisory Board.<ref name="governing committees"/> Each committee is made from members of different National Adhering Organizations from different countries.<ref name="nao">[http://www.iupac.org/nao IUPAC National Adhering Organizations] retrieved 15 April 2010</ref>

The steering committee hierarchy for IUPAC is as follows:<ref name="Project committee">[http://www.iupac.org/web/ins/013 IUPAC Project Committee] retrieved 15 April 2010</ref>
# All committees have an allotted budget that they must adhere to
# Any committee may start a project.
# If a project\'s spending becomes too much for a committee to continue funding, it must take the issue to the Project Committee.
# The project committee either increases the budget or decides on an external funding plan.
# The Bureau and Executive Committee oversee operations of the other committees

{| class="wikitable"
|+Committees Table
|-
! Committee name (abbreviation)
! Responsibilities
|-
| \'\'\'Bureau\'\'\'
|
* Discusses and makes changes to which committee has authority over a specific project
* Controls finances for all other committees and IUPAC as a whole
* Discusses general governance of IUPAC <ref name="bureau meeting">[http://old.iupac.org/news/archives/2007/84_bureau.html IUPAC news and references] retrieved 15 April 2010</ref>
|-
| \'\'\'CHEMRAWN Committee\'\'\' (Chem Research Applied to World Needs)
|
* Discusses different ways chemistry can and should be used to help the world<ref name="CHEMRAWN activities"/>
|-
| \'\'\'Committee on Chemistry Education\'\'\' (CCE)
|
* Coordinates IUPAC chemistry research with the educational systems of the world<ref name="Chemistry Education">[http://www.iupac.org/web/ins/050 Chemistry Education] retrieved 15 April 2010</ref>
|-
| \'\'\'Committee on Chemistry and Industry\'\'\' (COCI)
|
* Coordinates IUPAC chemistry research with [[industrial chemistry]] needs<ref name="Chemistry and Industry">[http://www.iupac.org/web/ins/050 Chemistry and Industry] retrieved 15 April 2010</ref>
|-
| \'\'\'Committee on Electronic and Printed Publications\'\'\' (CPEP)
|
* Designs and implements IUPAC publications
* Heads the Subcommittee on [[Spectroscopic]] Data Standards<ref name="Committee on Electronic and printed Publications">[http://www.iupac.org/web/ins/024 Committee on Electronic and Printed Publications webpage] retrieved 15 April 2010</ref>
|-
| \'\'\'Evaluation Committee\'\'\' (EvC)
|
* Evaluates every project
* Reports back to Executive committee on every project<ref name="Evaluation Committee"/>
|-
| \'\'\'Executive Committee\'\'\' (EC)
|
* Plans and discusses IUPAC events
* Discusses IUPAC fundraising
* Reviews other committees work<ref name="Executive Committee example meeting">[http://www.iupac.org/news/archives/2009/141_ec.pdf Executive Committee meeting] retrieved 15 April 2010</ref>

\'\'\'Current Officers of Executive Committee\'\'\':
* President: Moreau, Nicole J.
* Vice President: Tatsumi, Kazuyuki
* Treasurer: Corish, John
* Secretary General: Black, David StC.<ref name="executive committee">[http://www.iupac.org/web/ins/020 Executive Committee Page] retrieved 15 April 2010</ref>
|-
| \'\'\'Finance Committee\'\'\' (FC)
|
* Helps other committees properly manage their budget
* Advises Union officers on investments <ref name="Finanace Committee">[http://www.iupac.org/web/ins/026 Finance Committee web page] retrieved 15 April 2010</ref>
|-
| \'\'\'Interdivisional Committee on Terminology\'\'\' (ICTNS)
|
* Manages [[IUPAC Nomenclature]]
* Works through many projects to standardize nomenclature
* Standardizes measurements
* Discusses atomic weight standardization<ref name="Interdivisional Committee on Terminology"/>
|-
| \'\'\'Project Committee\'\'\' (PC)
|
* Manages funds that are under the jurisdiction of multiple projects
* Judges if a project is too large for its funding
* Recommends sources of external funding for projects
* Decides how to fund meetings in developing countries and countries in crisis<ref name="Project Committee">[http://www.iupac.org/web/ins/013 Project Committee web page] retrieved 15 April 2010</ref>
|-
| \'\'\'Pure and Applied Chemistry Editorial Advisory Board\'\'\' (PAC-EAB)
|
* Helps to plan, implement, and publish [[Pure and Applied Chemistry]]<ref name="Pure and Applied Chemistry Editorial Advisory Board"/>
|}

==Nomenclature==
The IUPAC committee has a long history of officially naming [[Organic compound|organic]] and [[inorganic compound]]s as mentioned in the Creation and History section. [[IUPAC nomenclature]] is developed so that any compound can be named under one set of standard rules to avoid repeat names. The first publication, which is information from the International Congress of Applied Chemistry,<ref name="publications">[http://www.iupac.org/indexes/books/years/1900 IUPAC Publications List] retrieved 15 April 2010</ref> is on [[IUPAC nomenclature of organic chemistry|IUPAC nomenclature of organic compounds]] can be found from the early 20th century in \'\'A Guide to IUPAC Nomenclature of Organic Compounds\'\' (1900).

===Organic nomenclature===
IUPAC organic nomenclature has three basic parts: the [[substituents]], [[carbon chain]] length and chemical ending.<ref name="Chemistry The Central Science"/> The [[substituents]] are any functional groups attached to the main carbon chain. The main carbon chain is the longest possible continuous chain. The chemical ending denotes what type of molecule it is. For example, the ending "ane" denotes a single bonded carbon chain, as in "hexane" ({{chem|C|6|H|14}}).<ref name="Organic Chemistry I As a Second Language: Translating the Basic Concepts">{{Cite book|last= Klein |first= David R. |title= Organic Chemistry I As a Second Language: Translating the Basic Concepts Second Edition |publisher= John Wiley & Sons Inc. |year= 2008 |isbn=978-0470-12929-6}}</ref>

Another example of [[IUPAC organic nomenclature]] is [[cyclohexanol]]:

[[Image:Cyclohexanol acsv.svg|thumb|150px|center|Cyclohexanol]]
* The substituent name for a [[ring compound]] is "cyclo".
* The indication (substituent name) for a six [[carbon chain]] is "hex".
* The chemical ending for a single bonded [[carbon chain]] is "an"
* The chemical ending for an [[alcohol]] is "ol"
* The two chemical endings are combined for an ending of "anol" indicating a single bonded carbon chain with an alcohol attached to it.<ref name="Chemistry The Central Science"/><ref name="Organic Chemistry I As a Second Language: Translating the Basic Concepts">{{Cite book|last= Klein |first= David R. |title= [[Organic Chemistry I As a Second Language: Translating the Basic Concepts Second Edition]] |publisher= [[John Wiley & Sons Inc.]] |year= 2008 |isbn= 13 978-0470-12929-6}}</ref><ref name="Gold Book second edition">{{cite web|url=http://old.iupac.org/publications/compendium/ |title=Gold Book web page |publisher=Old.iupac.org |date=2006-10-19 |accessdate=2011-06-08}}</ref>

===Inorganic nomenclature===
Basic IUPAC inorganic nomenclature has two main parts: the [[cation]] and the [[anion]]. The cation is the name for the positively charged [[ion]] and the anion is the name for the negatively charged [[ion]].<ref name="Chemistry The Central Science"/>

An example of [[IUPAC inorganic nomenclature]] is [[potassium chlorate]]:

[[Image:Potassium-chlorate-composition.png|thumb|150px|center|Potassium chlorate]]
* [[Potassium]] is the [[cation]] name.
* [[Chlorate]] is the [[anion]] name.<ref name="Chemistry The Central Science"/>

==Amino acid and nucleotide base codes==
IUPAC also has a system for giving codes to identify [[amino acid]]s and [[nucleotide]] bases. IUPAC needed a coding system that represented long sequences of amino acids. This would allow for these sequences to be compared to try to find [[homologies]].<ref name="Amino Acid">[http://www.chem.qmul.ac.uk/iupac/misc/naabb.html Amino Acid Codes] retrieved 15 April 2010</ref> These codes can consist of either a one letter code or a three letter code. For example:
* [[Alanine]]: Single letter code: A, Three letter code: Ala
These codes make it easier and shorter to write down the amino acid sequences that make up [[proteins]]. The nucleotide bases are made up of [[purines]] ([[adenine]] and [[guanine]]) and [[pyrimidines]] ([[cytosine]] and [[thymine]]). These nucleotide bases make up [[DNA]] and [[RNA]]. These nucleotide base codes make the genome of an organism much smaller and easier to read.<ref name="Amino">[http://www.ebi.ac.uk/2can/tutorials/aa.html Amino Acid and Nucleotide Base Codes] retrieved 15 April 2010</ref>

==Publications==
===Non-series books===
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Principles and Practices of Method Validation\'\'\'\'\'
|
\'\'Principles and Practices of Method Validation\'\' is a book entailing methods on validating and analyzing a many [[analyte]]s taken from a single [[aliquot]].<ref name="flipkart review of Principles and Practices of Method Validation">[http://www.flipkart.com/principles-practices-method-validation-fajgelj/0854047832-o8w3f3l1oc Flipkart Review of Principles and Practices of Method Validation] retrieved 15 April 2010</ref> Also, this book goes over techniques for analyzing many samples at once. Some methods discussed include: chromatographic methods, estimation of effects, matrix induced effects, and the effect of an equipment setup on an experiment.<ref name="flipkart review of Principles and Practices of Method Validation"/>
|-
| \'\'\'\'\'Fundamental Toxicology\'\'\'\'\'
|
\'\'Fundamental Toxicology\'\' is a textbook that proposes a [[curriculum]] for [[toxicology]] courses.<ref name="Fundamental Toxicology">[http://www.amazon.com/dp/0854046143 Fundamental Toxicology review on amazon] retrieved 15 April 2010</ref> \'\'Fundamental Toxicology\'\' is based on the book \'\'Fundamental Toxicology for Chemists\'\'.<ref name="Fundamental Toxicology Review">[http://www.rsc.org/Shop/books/2006/9780854046140.asp Fundamental Toxicology review on rsc.org] retrieved 15 April 2010</ref> \'\'Fundamental Toxicology\'\' is enhanced through many revisions and updates. New information added in the revisions includes: [[risk assessment]] and management; reproductive toxicology; behavioral toxicology; and [[ecotoxicology]].<ref name="Fundamental Toxicology Review">[http://www.rsc.org/Shop/books/2006/9780854046140.asp Fundamental Toxicology review on rsc.org] retrieved 15 April 2010</ref> This book is relatively well received as being useful for reviewing chemical [[toxicology]].<ref name="Fundamental Toxicology">[http://www.amazon.com/dp/0854046143 Fundamental Toxicology review on amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Macromolecular Symposia\'\'\'\'\'
|
\'\'Macromolecular Symposia\'\' is a journal that publishes fourteen issues a year. This journal includes contributions to the macromolecular chemistry and physics field. The meetings of the IUPAC are included in this journal along with the [[European Polymer Federation]], the [[American Chemical Society]], and the [[Society of Polymer Science]] in Japan.<ref name="Macromolecular Symposia">[http://old.iupac.org/publications/macro/index.html Macromolecular Symposia] retrieved 15 April 2010</ref>
|}

===Experimental Thermodynamics book series===
The Experimental Thermodynamics books series covers many topics in the fields of thermodynamics.

{| class="wikitable"
|-
! Book
! Description
|-
| \'\'\'\'\'Measurement of the Transport Properties of Fluids\'\'\'\'\'
|
\'\'Measurement of the Transport Properties of Fluids\'\' is a book that is published by [[Wiley-Blackwell|Blackwell Science Inc.]] The topics that are included in this book are low and high temperature measurements, secondary coefficients, [[Diffusion equation|diffusion coefficients]], [[light scattering]], transient methods for [[thermal conductivity]], methods for thermal conductivity, falling-body viscometers, and vibrating [[Rheometer|viscometers]].<ref name="Measurement of the Transport Properties of Fluids review on Amazon">[http://www.amazon.com/dp/0632029978 Measurement of the Transport Properties of Fluids review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Solution Calorimetry\'\'\'\'\'
|
\'\'Solution Calorimetry\'\' is a book that gives background information on [[thermal analysis]] and [[calorimetry]]. Thermoanalytical and calorimetric techniques along with thermodynamic and kinetic properties are discussed in this book. Later volumes of this book discusses the applications and principles of these thermodynamic and kinetic methods.<ref name="Solution Calorimetry review on Amazon">[http://www.amazon.com/dp/044482085X Solution Calorimetry review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Equations of State for Fluids and Fluid Mixtures Part I\'\'\'\'\'
|
\'\'Equations of State for Fluids and Fluid Mixtures Part I\'\' is a book that gives up to date equations of state for fluids and fluid mixtures. This book covers all ways to develop equations of state. It gives the strengths and weaknesses of each equation. Some equations discussed include: [[virial]] equation of state cubic equations; generalized [[van der Waals]] Equations; integral equations; perturbation theory; and stating and mixing rules. Other things that \'\'Equations of State for Fluids and Fluid Mixtures Part I\'\' goes over are: associating fluids, polymer systems, polydisperse fluids, self-assembled systems, ionic fluids, and fluids near their critical points.<ref name="Equations of State for Fluids and Fluid Mixtures Part I review on Amazon">[http://www.amazon.com/dp/0444503846 Equations of State for Fluids and Fluid Mixtures part I review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Measurement of the Thermodynamic Properties of Single Phases\'\'\'\'\'
|
\'\'Measurement of the Thermodynamic Properties of Single Phases\'\' is a book that gives an overview of techniques for measuring the thermodynamic quantities of single phases. It also goes into experimental techniques to test many different [[thermodynamic state]]s precisely and accurately. \'\'Measurement of the Thermodynamic Properties of Single Phases\'\' was written for people interested in measuring thermodynamic properties.<ref name="Flipkart review of Measurement of the Thermodynamic Properties of Single Phases">[http://www.flipkart.com/book/measurement-thermodynamic-properties-single-phases/0444509313 Flipkart review of Measurement of the Thermodynamic properties of Single Phases] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Measurement of the Thermodynamic Properties of Multiple Phases\'\'\'\'\'
|
\'\'Measurement of the Thermodynamic Properties of Multiple Phases\'\' is a book that includes multiple techniques that are used to study multiple phases of pure component systems. Also included in this book are the measurement techniques to obtain activity [[coefficients]], [[interfacial tension]], and [[Parameter|critical parameters]]. This book was written for researchers and graduate students as a reference source.<ref name="Measurement of the Thermodynamic Properties of Multiple Phases review on Amazon">[http://www.amazon.com/dp/0444519777 Measurement of the Thermodynamic Properties of Multiple Phases review on Amazon] retrieved 15 April 2010</ref>
|}

===Series of books on analytical and physical chemistry of environmental systems===
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Atmospheric Particles\'\'\'\'\'
|
\'\'Atmospheric Particles\'\' is a book that delves into aerosol science. This book is aimed as a reference for graduate students and atmospheric researchers. \'\'Atmospheric Particles\'\' goes in depth on the properties of aerosols in the atmosphere and their effect. Topics covered in this book are: [[acid rain]]; [[heavy metals|heavy metal]] pollution; [[global warming]]; and [[photochemical]] smog. \'\'Atmospheric Particles\'\' also covers techniques to analyze the atmosphere and ways to take atmospheric samples.<ref name="Atmospheric Particles review">[http://www.flipkart.com/book/atmospheric-particles-harrison-roy-rene/0471959359 Flipkart review of Atmospheric Particles] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\'\'\'\'
|
\'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\' is a book that discusses environmental [[colloid]]s and current information available on them. This book focuses on environmental colloids and particles in aquatic systems and soils. It also goes over techniques such as: techniques for sampling environmental colloids, size fractionation, and how to characterize of colloids and particles. \'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\' also delves into how these [[colloid]]s and [[particle]]s interact.<ref name="Environmental Colloids and Particles: Behaviour, Separation and Characterisation review on amazon">[http://www.amazon.com/dp/0470024321 Amazon Review of Environmental Colloids and Particles: Behaviour, Separation, and Characterisation] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\'\'\'\'
|
\'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\' is meant to give an overview of a technique based on [[fractal geometry]] and the processes of environmental systems. This book gives ideas on how to use [[fractal geometry]] to compare and contrast different [[ecosystems]]. It also gives an overview of the knowledge needed to solve environmental problems. Finally, \'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\' shows how to use the fractal approach to understand the reactivity of [[floc]]s, sediments, soils, microorganisms and [[humic]] substances.<ref name="Wiley page on Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems">[http://www.wiley.com/WileyCDA/WileyTitle/productCd-0470014741.html Wiley on Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems]. New York: Wiley. Retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\'\'\'\'
|
\'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' is meant to be read by chemists and biologists that study environmental systems. Also, this book should be used as a reference for earth scientists, environmental geologists, environmental engineers, and professionals in microbiology and ecology. \'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' is about how minerals, microorganisms, and organic components work together to affect [[terrestrial ecoregion|terrestrial systems]]. This book identifies that there are many different techniques and theories about minerals, microorganisms, and organic components individually, but they aren\'t often associated with each other. It further goes on to discuss how these components of soil work together to affect [[Terrestrial animal|terrestrial]] life. \'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' gives techniques to analyze minerals, microorganisms, and organic components together. This book also gives a large sections on why environmental scientists working in the specific fields of minerals, microorganisms, and organic components of soil should work together and how they should do so.<ref name="Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem review">[http://www.flipkart.com/book/interactions-between-soil-particles-microorganisms/0471607908 Flipkart review of Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem]. Retrieved 15 April 2010.</ref>
|-
| \'\'\'\'\'The Biogeochemistry of Iron in Seawater\'\'\'\'\'
|
\'\'The Biogeochemistry of Iron in Seawater\'\' is a book that describes how low concentrations of iron in [[Antarctica]] and the Pacific Oceans are a result of reduced chlorophyll for phytoplankton production.<ref name="SciTech Book News">SciTech Book News, Vol. 26, No. 2, June 2002.</ref> It does this by reviewing information from research in the 1990s. This book goes in depth about: chemical speciation; analytical techniques; transformation of iron; how iron limits the development of High Nutrient Low [[Chlorophyll]] areas in the [[pacific ocean]]<ref name="Amazon review of The Biogeochemistry of Iron in Seawater">[http://www.amazon.com/dp/0471490687 Review of Biogeochemistry of Iron in Seawater] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\'\'\'\'
|
\'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\' is a book that discusses techniques and devices to monitor [[marine ecoregion|aquatic systems]] and how new devices and techniques can be developed. This book emphasizes the future us of micro-analytical monitoring techniques and [[microtechnology]]. \'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\' is aimed at researchers and laboratories that analyze aquatic systems such as rivers, lakes, and oceans.<ref name="review of Insitu Monitoring of Aquatic Systems: Chemical Analysis and Speciation review">[http://search.barnesandnoble.com/In-Situ-Monitoring-of-Aquatic-Systems/Jacques-Buffle/e/9780471489795/ Review of \'\'In Situ\'\' Monitoring of Aquatic Systems: Chemical Analysis and Speciation from Barnes and Noble]. Retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Structure and Surface Reactions of Soil Particles\'\'\'\'\'
|
\'\'Structure and Surface Reactions of Soil Particles\'\' is a book about soil structures and the molecular processes that occur in soil. \'\'Structure and Surface Reactions of Soil Particles\'\' is aimed at any researcher researching soil or someone in the field of [[anthropology]]. It goes in depth on topics such as: fractal analysis of particle dimensions; computer modeling of the structure; reactivity of humics; applications of atomic force microscopy; and advanced instrumentation for analysis of soil particles.<ref name="Review of Structure and Surface Reactions of Soil Particles">[http://www.lebooks.in/books/structure-surface-reactions-soil-particles-pan-ming-huang-f605dd78c7/9780471959366 Review of Structure and Surface Reactions of Soil Particles] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Metal [[Speciation]] and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\'\'\'\'
|
\'\'Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\' is a book about the effect of [[trace metals]] on aquatic life.<ref name="Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3 review">[http://www.amazon.com/dp/0471958301 Metal Speciation and Bioavailability in Aquatic Systems]. Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3. Review on Amazon. Retrieved 15 April 2010</ref> This book is considered a specialty book for researchers interested in observing the effect of [[trace metals]] in the water supply. This book includes techniques to assess how [[bioassays]] can be used to evaluate how an [[organism]] is affected by [[trace metals]]. Also, \'\'Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\' looks at the limitations of the use of bioassays to observe the effects of [[trace metals]] on organisms.
|-
| \'\'\'\'\'Physicochemical Kinetics and Transport at Biointerfaces\'\'\'\'\'
|
\'\'Physicochemical Kinetics and Transport at Biointerfaces\'\' is a book created to aid [[environmental scientist]]s in field work. The book gives an overview of chemical mechanisms, transport, kinetics, and interactions that occur in [[Environment (biophysical)|environmental systems]]. \'\'Physicochemical Kinetics and Transport at Biointerfaces continues from where \'\'Metal Speciation and Bioavailability in Aquatic Systems\'\' leaves off.<ref name="Physicochemical Kinetics and Transport at Biointerfaces review">[http://www.amazon.com/dp/0471498459 Physicochemical Kinetics and Transport at Biointerfaces review] retrieved 15 April 2010</ref>
|}

===Colored cover book and website series (nomenclature)===
IUPAC color codes their books in order to make each book distinguishable. Books that follow this trend are: [[Compendium of Analytical Nomenclature]]; [[Pure and Applied Chemistry]](journal); and [[Compendium of Chemical Terminology]].<ref name="History of IUPAC"/>
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Compendium of Analytical Nomenclature\'\'\'\'\'
|
One extensive book on almost all nomenclature written ([[IUPAC nomenclature of organic chemistry]] and [[IUPAC nomenclature of inorganic chemistry]]) by the IUPAC committee is [[Compendium of Analytical Nomenclature]] - The Orange Book, 1st edition (1978)<ref name="orange book">[http://www.iupac.org/objID/Source/sou89661252938339267969131 IUPAC orange book publication history]</ref> This book was revised in 1987. The second edition has many revisions that come from reports on nomenclature between 1976 and 1984.<ref name="orange book preamble">[http://old.iupac.org/publications/analytical_compendium/ Orange Book Preamble] retrieved 15 April 2010</ref> In 1992, the second edition went through many different revisions which led to the third edition.<ref name="orange book preamble">[http://old.iupac.org/publications/analytical_compendium/ Orange Book Preamble] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Pure and Applied Chemistry\'\'\'\'\'
|
[[Pure and Applied Chemistry]] is the official monthly journal of IUPAC. This journal first debuted in 1960. The goal statement for [[Pure and Applied Chemistry]] is to "publish highly topical and credible works at the forefront of all aspects of pure and applied chemistry."<ref name="offical journal">[http://www.iupac.org/publications/pac/ IUPAC Pure and Applied Chemistry] retrieved 15 April 2010</ref> The Journal itself is available by subscription, but older issues are available in the archive on the IUPAC website.

[[Pure and Applied Chemistry]] was created as a central way to publish IUPAC endorsed articles.<ref name="First journal">[http://media.iupac.org/publications/pac/1960/pdf/0101x0003.pdf IUPAC Pure and Applied Chemistry Issue 1] retrieved 15 April 2010</ref> Before its creation, IUPAC didn\'t have a quick, official way to distribute new chemistry information.

Its creation was first suggested at The Paris IUPAC Meeting of 1957.<ref name="First journal">[http://media.iupac.org/publications/pac/1960/pdf/0101x0003.pdf IUPAC Pure and Applied Chemistry Issue 1] retrieved 15 April 2010</ref> During this meeting the commercial publisher of the Journal was discussed and decided on. In 1959, the IUPAC Pure and Applied Chemistry Editorial Advisory Board was created put in charge of the journal. The idea of one journal being a definitive place for a vast amount of chemistry was difficult for the committee to grasp at first.<ref name="First journal"/> However, it was decided that the journal would reprint old journal editions to keep all chemistry knowledge available.
|-
| \'\'\'\'\'Compendium of Chemical Terminology\'\'\'\'\'
|
The [[Compendium of Chemical Terminology]], also known as The Gold Book, was originally worked on by [[Victor Gold]]. This book is a collection of names and terms already discussed in [[Pure and Applied Chemistry]].<ref name="Gold Book">[http://goldbook.iupac.org/about.html Gold Book Online] retrieved 15 April 2010</ref> [[Compendium of Chemical Terminology]] was first published in 1987.<ref name="History of IUPAC"/> The first edition of this book contains no original material, but is meant to be a compilation of other IUPAC works.

The second edition of this book was published in 1997.<ref name="Gold Book second edition">[http://old.iupac.org/publications/compendium/] retrieved 15 April 2010</ref> This book made large changes to the first edition of The [[Compendium of Chemical Terminology]]. These changes included updated material and an expansion of the book to include over seven thousand terms.<ref name="Gold Book second edition">[http://old.iupac.org/publications/compendium/] retrieved 15 April 2010</ref> The second edition was the topic of an IUPAC [[XML]] project. This project made an [[XML]] version of the book that includes over seven thousand terms. The [[XML]] version of the book includes an open editing policy, which allows users to add excerpts of the written version.<ref name="Gold Book second edition"/>
|-
| \'\'\'\'\'IUPAC Nomenclature of Organic Chemistry (Online Publication)\'\'\'\'\'
| \'\'IUPAC Nomenclature of Organic Chemistry\'\' (publication), also known as The Blue Book, is a website published by Advanced Chemistry Department Incorporated with the permission of IUPAC. This site is a compilation of the books \'\'A Guide to IUPAC Nomenclature of Organic Compounds\'\' and \'\'Nomenclature of Organic Chemistry\'\'.<ref name="Blue Book">[http://www.acdlabs.com/iupac/nomenclature/ Online version of Blue Book] retrieved 15 April 2010</ref>
|}

==International Year of Chemistry==
[[Image:Internationalyearofchemistrylogo.png|thumb|upright|alt=A red square behind an orange square, which is behind a blue square that says "2011 C Chemistry" on it. Under this, there are the words "International Year of Chemistry 2011".|150px|International Year of Chemistry Logo|left| ]]

IUPAC and [[UNESCO]] are the lead organizations coordinating events for the [[International Year of Chemistry]], which will take place in 2011.<ref>[http://freitag.creighton.edu/OmahaACSfiles/N0848333.pdf United Nations Resolution 63/209: International Year of Chemistry.] February 3, 2009. Retrieved on April 24, 2010.</ref><ref name="note1">[http://www.chemistry2011.org/assets/42/IYC_prospectus.pdf About IYC: Introduction.] July 9, 2009. Retrieved on April 24, 2010.</ref> The [[International Year of Chemistry]] was originally proposed by IUPAC at the General Assembly in [[Turin]], [[Italy]].<ref name="International Year of Chemistry Prospectus">{{cite web|url=http://portal.acs.org/portal/PublicWebSite/membership/acs/getinvolved/CNBP_021696 |title=International Year of Chemistry Prospectus |publisher=Portal.acs.org |date= |accessdate=2011-06-08}}</ref> This motion was adopted by [[UNESCO]] at a meeting in 2008.<ref name="International Year of Chemistry Prospectus">[http://portal.acs.org/portal/PublicWebSite/membership/acs/getinvolved/CNBP_021696 International Year of Chemistry Prospectus] retrieved 15 April 2010</ref> The main objectives of the [[International Year of Chemistry]] is to increase public appreciation of chemistry and gain more interest in the world of [[chemistry]]. This event is also being held to encourage young people to get involved and contribute to [[chemistry]]. A further reason for this event being held is to honour how chemistry has made improvements to everyone\'s way of life.<ref name="IYC">[http://www.chemistry2011.org/about-iyc/introduction IYC: Introduction.] July 9, 2009. Retrieved on February 17, 2010. retrieved 15 April 2010</ref>

==Current projects==
===IUPAC current project list===
* Project Number 2009-012-2-200: [[Coordination polymers]] and [[metal organic framework]]s: terminology and nomenclature guidelines <ref name="IUPAC Current Projects">[http://www.iupac.org/indexes/Projects/years/2009 IUPAC Current Projects.] May 09, 2010. Retrieved on May 09, 2010.</ref>
** The objectives of this project are (1) to produce guidelines for terminology (glossary of terms) and [[nomenclature]] (concerning [[topology]], not naming of individual substances) in the area of [[coordination polymers]], (2) to ensure that these guidelines are accepted by a large group of leading researchers in the field, and (3) to have these guidelines implemented or referred to in the instructions to authors of leading general and [[inorganic chemistry]] journals.<ref name="CP and MOF Project">[http://www.iupac.org/web/ins/2009-012-2-200 CP and MOF Project.] May 09, 2010. Retrieved on May 09, 2010.</ref>
* Project Number 2009-032-1-100: Categorizing Halogen [[Chemical bond|Bonding]] and Other [[Noncovalent]] Interactions Involving [[Halogen]] Atoms<ref name="IUPAC Current Projects">[http://www.iupac.org/indexes/Projects/years/2010 IUPAC Current Projects.] February 15, 2010. Retrieved on February 17, 2010.</ref>
** The objective of this project is to give a modern definition to the term [[halogen]] bonding and to examine and classify [[halogens]] as [[electrophilic]] species and their [[intermolecular]] interactions.<ref name="Halogen Bonding Project">[http://www.iupac.org/web/ins/2009-032-1-100 Halogen Bonding Project.] February 15, 2010. Retrieved on February 17, 2010.</ref>
* Project Number 2009-048-1-600: Guidance for substance-related environmental monitoring strategies regarding soil and surface water<ref name="IUPAC Current Projects"/>
** The objective of this project is to identify new pollutants and their hazards and to monitor less investigated pollutants. Also, this project will provide strategies for how pollutants should be monitored. The advantages and disadvantages of each monitoring technique will be discussed.<ref name="Pollutant Project">[http://www.iupac.org/web/ins/2009-048-1-600 IUPAC Current Projects.] February 15, 2010. Retrieved on March 2, 2010. retrieved 15 April 2010</ref>
* Project Number 2009-034-2-700: [[Risk Assessment]] of Effects of [[Cadmium]] on Human Health<ref name="IUPAC Current Projects"/>
** The objective of this project is to identify the risks and effects of exposure of humans to [[Cadmium]], which is classified as a carcinogenic to humans by the International Agency for Research on [[Cancer]]. Also, the objective includes researching how [[Cadmium]] enters into the human body.<ref name="Cadium">[http://www.iupac.org/web/ins/2009-034-2-700 IUPAC Current Projects.] February 15, 2010. Retrieved on March 2, 2010.</ref>
* Project Number 2009-019-2-400: Data Treatment in SEC and Other Techniques of [[Polymer]] Characterization. Correction for Band Broadening and Other Sources of Error.<ref name="IUPAC Current Projects"/>
** The objective of this project is to provide practical alternatives for improving the accuracy of [[polymer]] characterization and measurements. This would allow manufacturers of equipment, such as [[Size exclusion chromatography|Size exclusive chromatography]] (SEC) and other [[polymer]] characterization techniques, to sell a product that is more accurate and precise.<ref name="IUPAC Current Projects"/>

==See also==
* [[CAS registry number]]
* [[Element naming controversy]]
* [[International Union of Biochemistry and Molecular Biology]] (IUBMB)
* [[International Chemical Identifier]] (InChI)
* [[Institute for Reference Materials and Measurements]] (IRMM)
* [[National Institute of Standards and Technology]] (NIST)
* [[International Union of Pure and Applied Chemistry nomenclature]]
* [[European Association for Chemical and Molecular Sciences]]

==References==
{{Reflist|colwidth=30em}}

==External links==
* {{official|http://www.iupac.org}}
*{{cite web|url=http://www.chem.qmul.ac.uk/iubmb/thermod/|title=Recommendations for nomenclature and tables in biochemical thermodynamics|author=Panel on Biochemical Thermodynamics|year=1994|publisher=G. P. Moss, [[Queen Mary University of London]]}}
{{Good article}}

{{International Council for Science}}

{{DEFAULTSORT:International Union Of Pure And Applied Chemistry}}
[[Category:Chemistry organizations]]
[[Category:International scientific organizations]]
[[Category:Standards organizations]]
[[Category:Chemical nomenclature]]
[[Category:Organizations established in 1919]]

[[af:IUPAC]]
[[ar:الاتحاد الدولي للكيمياء البحتة والتطبيقية]]
[[az:Beynəlxalq Nəzəri və Tətbiqi Kimya İttifaqı]]
[[be:IUPAC]]
[[bs:IUPAC]]
[[br:Unaniezh Etrevroadel ar Gimiezh Pur hag Arveret]]
[[bg:Международен съюз за чиста и приложна химия]]
[[ca:Unió Internacional de Química Pura i Aplicada]]
[[cs:Mezinárodní unie pro čistou a užitou chemii]]
[[cy:IUPAC]]
[[da:International Union of Pure and Applied Chemistry]]
[[de:International Union of Pure and Applied Chemistry]]
[[el:Διεθνής Ένωση Καθαρής και Εφαρμοσμένης Χημείας]]
[[es:Unión Internacional de Química Pura y Aplicada]]
[[eo:IUPAK]]
[[fa:اتحادیه بین‌المللی شیمی محض و کاربردی]]
[[fr:Union internationale de chimie pure et appliquée]]
[[gl:IUPAC]]
[[ko:국제순수·응용화학연합]]
[[hi:शुद्ध और अनुप्रयोगिक रसायन का अंतरराष्ट्रीय संघ]]
[[hr:Međunarodna unija za čistu i primijenjenu kemiju]]
[[id:International Union of Pure and Applied Chemistry]]
[[it:IUPAC]]
[[ka:თეორიული და გამოყენებითი ქიმიის საერთაშორისო კავშირი]]
[[lb:International Union of Pure and Applied Chemistry]]
[[lmo:IUPAC]]
[[hu:IUPAC]]
[[mk:Меѓународна унија за чиста и применета хемија]]
[[ml:ഇന്റർനാഷണൽ യൂണിയൻ ഓഫ് പ്യുർ ആന്റ് അപ്ലൈഡ് കെമിസ്ട്രി]]
[[ms:IUPAC]]
[[nl:IUPAC]]
[[ja:国際純正・応用化学連合]]
[[no:IUPAC]]
[[nds:International Union of Pure and Applied Chemistry]]
[[pl:Międzynarodowa Unia Chemii Czystej i Stosowanej]]
[[pt:União Internacional de Química Pura e Aplicada]]
[[ro:Uniunea Internațională de Chimie Pură și Aplicată]]
[[ru:Международный союз теоретической и прикладной химии]]
[[sq:IUPAC]]
[[simple:IUPAC]]
[[sk:Medzinárodná únia čistej a aplikovanej chémie]]
[[sl:Mednarodna zveza za čisto in uporabno kemijo]]
[[sr:Међународна унија за чисту и примењену хемију]]
[[sh:IUPAC]]
[[fi:IUPAC]]
[[sv:International Union of Pure and Applied Chemistry]]
[[ta:தனி மற்றும் பயன்பாட்டு வேதியியல் அனைத்துலக ஒன்றியம்]]
[[tr:Uluslararası Temel ve Uygulamalı Kimya Birliği]]
[[uk:IUPAC]]
[[ur:بین الاقوامی اتحاد براۓ خالص و نفاذی کیمیاء]]
[[vi:IUPAC]]
[[wuu:优八克]]
[[zh:國際純粹與應用化學聯合會]]"""
h=hasher(text)
''', setup_stmt)
res = t.timeit()
print 'Processing time murmur hash: %s' % res

setup_stmt = 'import hashlib; hasher = hashlib.md5()'
t = timeit.Timer('''
text = """{{redirect-distinguish|IUPAC|International Union of Painters and Allied Trades|IUPAT}}\
[[Image:IUPAC.svg|thumb|upright|alt=A black and white image of an old fashioned scale with two spheres below it. Below the spheres is some chemistry apparati.|150px|IUPAC logo]]
The \'\'\'International Union of Pure and Applied Chemistry\'\'\' (\'\'\'IUPAC\'\'\', pronounced {{IPA-en|ˈaɪjuːpæk|}} {{respell|EYE|ew-pak}} or {{IPA-en|ˈjuːpæk|}} {{respell|EW|pak}}) is an international federation of [[National Adhering Organizations]] that represents chemists in individual countries. It is a member of the [[International Council for Science]] (ICSU).<ref name="nao">{{cite web|url=http://www.iupac.org/nao |title=IUPAC National Adhering Organizations |publisher=Iupac.org |date=2011-06-02 |accessdate=2011-06-08}}</ref> The international headquarters of IUPAC is located in [[Zürich]], [[Switzerland]]. The administrative office, known as the "IUPAC Secretariat", is located in [[Research Triangle Park]], [[North Carolina]], [[United States]]. This administrative office is headed by the IUPAC executive director.<ref>{{cite web|url=http://old.iupac.org/symposia/conferences/ga09/Council_Agenda_Book_2009.pdf|title=IUPAC Council Agenda Book 2009|publisher=IUPAC|accessdate=2010-04-17|year=2009}}</ref>

IUPAC was established in 1919 as the successor of the International Congress of Applied Chemistry for the advancement of [[chemistry]]. Its members, the National Adhering Organizations, can be national [[List of chemistry societies|chemistry societies]], national [[Academy of Sciences|academies of sciences]], or other bodies representing chemists. There are fifty-four National Adhering Organizations and three Associate National Adhering Organizations.<ref name="nao"/> IUPAC\'s Inter-divisional Committee on Nomenclature and Symbols ([[International Union of Pure and Applied Chemistry nomenclature|IUPAC nomenclature]]) is the recognized world authority in developing standards for the naming of the [[chemical elements]] and [[chemical compounds|compounds]]. Since its creation, IUPAC has been run by many different committees with different responsibilities.<ref name="governing committees">[http://www.iupac.org/Organization/Committees IUPAC Committees list] retrieved 15 April 2010</ref> These committees all run different projects which include standardizing [[nomenclature]],<ref name="Interdivisional Committee on Terminology">[http://www.iupac.org/web/ins/027 Interdivisional Committee on Terminology web page] retrieved 15 April 2010</ref> finding ways to bring [[chemistry]] to the world,<ref name="CHEMRAWN activities">[http://www.iupac.org/news/archives/2007/44th_council/Item_12-2_2007.pdf Chemdrawn] retrieved 15 April 2010</ref> and publishing works.<ref name="Pure and Applied Chemistry Editorial Advisory Board">[http://www.iupac.org/web/ins/030 Pure and Applied Chemistry Editorial Advisory Board web page] retrieved 15 April 2010</ref><ref name="Project Committee">{{cite web|url=http://www.iupac.org/web/ins/013 |title=Project Committee web page |publisher=Iupac.org |date=2011-06-02 |accessdate=2011-06-08}}</ref><ref name="Evaluation Committee">[http://www.iupac.org/web/ins/014 Evaluation Committee page] retrieved 15 April 2010</ref>

IUPAC is best known for its works standardizing nomenclature in chemistry and other fields of science, but IUPAC has publications in many fields including chemistry, biology and physics.<ref name="History of IUPAC"/> Some important work IUPAC has done in these fields includes standardizing [[nucleotide]] base sequence code names; publishing books for environmental scientists, chemists, and physicists; and leading the way in improving education in science.<ref name="History of IUPAC"/><ref name="IYC">[http://www.chemistry2011.org/about-iyc/introduction IYC: Introduction.] July 9, 2009. Retrieved on February 17, 2010. retrieved 15 April 2010</ref>

==Creation and history==
[[Image:Frkekulé.jpg|thumb|upright|alt=A black and white image of a bald man in a dark outfit, with a bushy white beard and moustache|Friedrich August Kekulé von Stradonitz]]The need for an international standard for chemistry was first addressed in 1860 by a committee headed by German scientist [[Friedrich August Kekulé von Stradonitz]]. This committee was the first international conference to create an international naming system for [[organic compounds]].<ref name="History of IUPAC">{{Cite book|last= Fennel |first= R.W. |title= History of IUPAC, 1919-1987 |publisher= Blackwell Science |year= 1994 |isbn= 0-86542-8786(94)}}</ref> The ideas that were formulated in that conference evolved into the official [[IUPAC nomenclature of organic chemistry]].<ref name="History of IUPAC"/> The IUPAC stands as a legacy of this meeting, making it one of the most important historical international collaborations of [[List of chemistry societies|chemistry societies]].<ref name="History of IUPAC"/>

The ideas of committee of 1860 were further addressed by the first international proposal in 1892. The rules established by this proposal are known as the [[Geneva Rules]]. The Geneva Rules first officially standardized some [[Organic Chemistry]] names and rules. The largest contribution that the Geneva Rules made to Organic Chemistry is the establishment of Organic [[Root (linguistics)|root names]].<ref name="first proposal committee">[http://www.patentstorm.us/patents/4747059/description.html Beginnings of standardization] retrieved 15 April 2010</ref> The conference in [[Geneva]] was held by a chemistry society that also was precursor to IUPAC called The International Union of Chemistry.<ref name="Chemistry The Central Science">{{Cite book|last= Brown |first= Theodore L. |coauthors= H. Eugene LeMay Jr, Bruce E Bursten |title= Chemistry The Central Science Tenth Edition |publisher= Pearson Books |year= 2006 |isbn= 0-13-109686-9}}</ref>

The work from the first international proposal was continued by the Commission for the Reform of [[Nomenclature]] in [[Organic Chemistry]]. The Commission for the Reform of Nomenclature was headed by The International Union of Chemistry.<ref name="first proposal committee"/> The International Union of Chemistry continued to work on the naming of Organic compounds until the advent of [[World War I]], when international communications got difficult.<ref name="History of IUPAC"/>

After World War I, discussion ensued about the formation of a new permanent international chemistry society. At this time, the basic [[nomenclature]] of [[Organic compounds]] was established. However, a new organization needed to be established in order to continue work on the standardizing of chemistry.<ref name="History of IUPAC"/> This prompted the creation of the International Union of Pure and Applied Chemistry (IUPAC) in 1919. Since this time, IUPAC has been the official organization held with the responsibility of updating and maintaining official [[organic nomenclature]].<ref name="Chemistry The Central Science"/> One notable country excluded from the early IUPAC was [[Germany]]. Germany\'s exclusion was a result of prejudice towards Germans by the allied powers after [[World War I]]<ref name="Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts">{{Cite book|last= Kaderas |first= Brigitte |title= Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts|language=German |publisher= Franz Steiner Verlag |year= 2002 |isbn= 3-515-08111-9}}</ref> [[Germany]] was finally admitted into IUPAC during 1929. However, [[Nazi Germany]] was removed from IUPAC during [[World War II]]

During World War II, IUPAC was affiliated with the [[allied powers]], but had little involvement during the war effort itself. After the war, West [[Germany]] was allowed back into IUPAC.<ref name="Wissenschaften und Wissenschaftspolitik: Bestandsaufnahmen zu Formationen, Brüchen und Kontinuitäten im Deutschland des 20. Jahrhunderts"/> Since World War II, IUPAC has been focused on standardizing nomenclature and methods in science without interruption.

==Committees and governance==
IUPAC is governed by several [[committees]] that all have different responsibilities. The committees are as follows: Bureau, CHEMRAWN (Chem Research Applied to World Needs) Committee, Committee on Chemistry Education, Committee on Chemistry and Industry, Committee on Printed and Electronic Publications, Evaluation Committee, Executive Committee, Finance Committee, Interdivisional Committee on Terminology, Nomenclature and Symbols, Project Committee, Pure and Applied Chemistry Editorial Advisory Board.<ref name="governing committees"/> Each committee is made from members of different National Adhering Organizations from different countries.<ref name="nao">[http://www.iupac.org/nao IUPAC National Adhering Organizations] retrieved 15 April 2010</ref>

The steering committee hierarchy for IUPAC is as follows:<ref name="Project committee">[http://www.iupac.org/web/ins/013 IUPAC Project Committee] retrieved 15 April 2010</ref>
# All committees have an allotted budget that they must adhere to
# Any committee may start a project.
# If a project\'s spending becomes too much for a committee to continue funding, it must take the issue to the Project Committee.
# The project committee either increases the budget or decides on an external funding plan.
# The Bureau and Executive Committee oversee operations of the other committees

{| class="wikitable"
|+Committees Table
|-
! Committee name (abbreviation)
! Responsibilities
|-
| \'\'\'Bureau\'\'\'
|
* Discusses and makes changes to which committee has authority over a specific project
* Controls finances for all other committees and IUPAC as a whole
* Discusses general governance of IUPAC <ref name="bureau meeting">[http://old.iupac.org/news/archives/2007/84_bureau.html IUPAC news and references] retrieved 15 April 2010</ref>
|-
| \'\'\'CHEMRAWN Committee\'\'\' (Chem Research Applied to World Needs)
|
* Discusses different ways chemistry can and should be used to help the world<ref name="CHEMRAWN activities"/>
|-
| \'\'\'Committee on Chemistry Education\'\'\' (CCE)
|
* Coordinates IUPAC chemistry research with the educational systems of the world<ref name="Chemistry Education">[http://www.iupac.org/web/ins/050 Chemistry Education] retrieved 15 April 2010</ref>
|-
| \'\'\'Committee on Chemistry and Industry\'\'\' (COCI)
|
* Coordinates IUPAC chemistry research with [[industrial chemistry]] needs<ref name="Chemistry and Industry">[http://www.iupac.org/web/ins/050 Chemistry and Industry] retrieved 15 April 2010</ref>
|-
| \'\'\'Committee on Electronic and Printed Publications\'\'\' (CPEP)
|
* Designs and implements IUPAC publications
* Heads the Subcommittee on [[Spectroscopic]] Data Standards<ref name="Committee on Electronic and printed Publications">[http://www.iupac.org/web/ins/024 Committee on Electronic and Printed Publications webpage] retrieved 15 April 2010</ref>
|-
| \'\'\'Evaluation Committee\'\'\' (EvC)
|
* Evaluates every project
* Reports back to Executive committee on every project<ref name="Evaluation Committee"/>
|-
| \'\'\'Executive Committee\'\'\' (EC)
|
* Plans and discusses IUPAC events
* Discusses IUPAC fundraising
* Reviews other committees work<ref name="Executive Committee example meeting">[http://www.iupac.org/news/archives/2009/141_ec.pdf Executive Committee meeting] retrieved 15 April 2010</ref>

\'\'\'Current Officers of Executive Committee\'\'\':
* President: Moreau, Nicole J.
* Vice President: Tatsumi, Kazuyuki
* Treasurer: Corish, John
* Secretary General: Black, David StC.<ref name="executive committee">[http://www.iupac.org/web/ins/020 Executive Committee Page] retrieved 15 April 2010</ref>
|-
| \'\'\'Finance Committee\'\'\' (FC)
|
* Helps other committees properly manage their budget
* Advises Union officers on investments <ref name="Finanace Committee">[http://www.iupac.org/web/ins/026 Finance Committee web page] retrieved 15 April 2010</ref>
|-
| \'\'\'Interdivisional Committee on Terminology\'\'\' (ICTNS)
|
* Manages [[IUPAC Nomenclature]]
* Works through many projects to standardize nomenclature
* Standardizes measurements
* Discusses atomic weight standardization<ref name="Interdivisional Committee on Terminology"/>
|-
| \'\'\'Project Committee\'\'\' (PC)
|
* Manages funds that are under the jurisdiction of multiple projects
* Judges if a project is too large for its funding
* Recommends sources of external funding for projects
* Decides how to fund meetings in developing countries and countries in crisis<ref name="Project Committee">[http://www.iupac.org/web/ins/013 Project Committee web page] retrieved 15 April 2010</ref>
|-
| \'\'\'Pure and Applied Chemistry Editorial Advisory Board\'\'\' (PAC-EAB)
|
* Helps to plan, implement, and publish [[Pure and Applied Chemistry]]<ref name="Pure and Applied Chemistry Editorial Advisory Board"/>
|}

==Nomenclature==
The IUPAC committee has a long history of officially naming [[Organic compound|organic]] and [[inorganic compound]]s as mentioned in the Creation and History section. [[IUPAC nomenclature]] is developed so that any compound can be named under one set of standard rules to avoid repeat names. The first publication, which is information from the International Congress of Applied Chemistry,<ref name="publications">[http://www.iupac.org/indexes/books/years/1900 IUPAC Publications List] retrieved 15 April 2010</ref> is on [[IUPAC nomenclature of organic chemistry|IUPAC nomenclature of organic compounds]] can be found from the early 20th century in \'\'A Guide to IUPAC Nomenclature of Organic Compounds\'\' (1900).

===Organic nomenclature===
IUPAC organic nomenclature has three basic parts: the [[substituents]], [[carbon chain]] length and chemical ending.<ref name="Chemistry The Central Science"/> The [[substituents]] are any functional groups attached to the main carbon chain. The main carbon chain is the longest possible continuous chain. The chemical ending denotes what type of molecule it is. For example, the ending "ane" denotes a single bonded carbon chain, as in "hexane" ({{chem|C|6|H|14}}).<ref name="Organic Chemistry I As a Second Language: Translating the Basic Concepts">{{Cite book|last= Klein |first= David R. |title= Organic Chemistry I As a Second Language: Translating the Basic Concepts Second Edition |publisher= John Wiley & Sons Inc. |year= 2008 |isbn=978-0470-12929-6}}</ref>

Another example of [[IUPAC organic nomenclature]] is [[cyclohexanol]]:

[[Image:Cyclohexanol acsv.svg|thumb|150px|center|Cyclohexanol]]
* The substituent name for a [[ring compound]] is "cyclo".
* The indication (substituent name) for a six [[carbon chain]] is "hex".
* The chemical ending for a single bonded [[carbon chain]] is "an"
* The chemical ending for an [[alcohol]] is "ol"
* The two chemical endings are combined for an ending of "anol" indicating a single bonded carbon chain with an alcohol attached to it.<ref name="Chemistry The Central Science"/><ref name="Organic Chemistry I As a Second Language: Translating the Basic Concepts">{{Cite book|last= Klein |first= David R. |title= [[Organic Chemistry I As a Second Language: Translating the Basic Concepts Second Edition]] |publisher= [[John Wiley & Sons Inc.]] |year= 2008 |isbn= 13 978-0470-12929-6}}</ref><ref name="Gold Book second edition">{{cite web|url=http://old.iupac.org/publications/compendium/ |title=Gold Book web page |publisher=Old.iupac.org |date=2006-10-19 |accessdate=2011-06-08}}</ref>

===Inorganic nomenclature===
Basic IUPAC inorganic nomenclature has two main parts: the [[cation]] and the [[anion]]. The cation is the name for the positively charged [[ion]] and the anion is the name for the negatively charged [[ion]].<ref name="Chemistry The Central Science"/>

An example of [[IUPAC inorganic nomenclature]] is [[potassium chlorate]]:

[[Image:Potassium-chlorate-composition.png|thumb|150px|center|Potassium chlorate]]
* [[Potassium]] is the [[cation]] name.
* [[Chlorate]] is the [[anion]] name.<ref name="Chemistry The Central Science"/>

==Amino acid and nucleotide base codes==
IUPAC also has a system for giving codes to identify [[amino acid]]s and [[nucleotide]] bases. IUPAC needed a coding system that represented long sequences of amino acids. This would allow for these sequences to be compared to try to find [[homologies]].<ref name="Amino Acid">[http://www.chem.qmul.ac.uk/iupac/misc/naabb.html Amino Acid Codes] retrieved 15 April 2010</ref> These codes can consist of either a one letter code or a three letter code. For example:
* [[Alanine]]: Single letter code: A, Three letter code: Ala
These codes make it easier and shorter to write down the amino acid sequences that make up [[proteins]]. The nucleotide bases are made up of [[purines]] ([[adenine]] and [[guanine]]) and [[pyrimidines]] ([[cytosine]] and [[thymine]]). These nucleotide bases make up [[DNA]] and [[RNA]]. These nucleotide base codes make the genome of an organism much smaller and easier to read.<ref name="Amino">[http://www.ebi.ac.uk/2can/tutorials/aa.html Amino Acid and Nucleotide Base Codes] retrieved 15 April 2010</ref>

==Publications==
===Non-series books===
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Principles and Practices of Method Validation\'\'\'\'\'
|
\'\'Principles and Practices of Method Validation\'\' is a book entailing methods on validating and analyzing a many [[analyte]]s taken from a single [[aliquot]].<ref name="flipkart review of Principles and Practices of Method Validation">[http://www.flipkart.com/principles-practices-method-validation-fajgelj/0854047832-o8w3f3l1oc Flipkart Review of Principles and Practices of Method Validation] retrieved 15 April 2010</ref> Also, this book goes over techniques for analyzing many samples at once. Some methods discussed include: chromatographic methods, estimation of effects, matrix induced effects, and the effect of an equipment setup on an experiment.<ref name="flipkart review of Principles and Practices of Method Validation"/>
|-
| \'\'\'\'\'Fundamental Toxicology\'\'\'\'\'
|
\'\'Fundamental Toxicology\'\' is a textbook that proposes a [[curriculum]] for [[toxicology]] courses.<ref name="Fundamental Toxicology">[http://www.amazon.com/dp/0854046143 Fundamental Toxicology review on amazon] retrieved 15 April 2010</ref> \'\'Fundamental Toxicology\'\' is based on the book \'\'Fundamental Toxicology for Chemists\'\'.<ref name="Fundamental Toxicology Review">[http://www.rsc.org/Shop/books/2006/9780854046140.asp Fundamental Toxicology review on rsc.org] retrieved 15 April 2010</ref> \'\'Fundamental Toxicology\'\' is enhanced through many revisions and updates. New information added in the revisions includes: [[risk assessment]] and management; reproductive toxicology; behavioral toxicology; and [[ecotoxicology]].<ref name="Fundamental Toxicology Review">[http://www.rsc.org/Shop/books/2006/9780854046140.asp Fundamental Toxicology review on rsc.org] retrieved 15 April 2010</ref> This book is relatively well received as being useful for reviewing chemical [[toxicology]].<ref name="Fundamental Toxicology">[http://www.amazon.com/dp/0854046143 Fundamental Toxicology review on amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Macromolecular Symposia\'\'\'\'\'
|
\'\'Macromolecular Symposia\'\' is a journal that publishes fourteen issues a year. This journal includes contributions to the macromolecular chemistry and physics field. The meetings of the IUPAC are included in this journal along with the [[European Polymer Federation]], the [[American Chemical Society]], and the [[Society of Polymer Science]] in Japan.<ref name="Macromolecular Symposia">[http://old.iupac.org/publications/macro/index.html Macromolecular Symposia] retrieved 15 April 2010</ref>
|}

===Experimental Thermodynamics book series===
The Experimental Thermodynamics books series covers many topics in the fields of thermodynamics.

{| class="wikitable"
|-
! Book
! Description
|-
| \'\'\'\'\'Measurement of the Transport Properties of Fluids\'\'\'\'\'
|
\'\'Measurement of the Transport Properties of Fluids\'\' is a book that is published by [[Wiley-Blackwell|Blackwell Science Inc.]] The topics that are included in this book are low and high temperature measurements, secondary coefficients, [[Diffusion equation|diffusion coefficients]], [[light scattering]], transient methods for [[thermal conductivity]], methods for thermal conductivity, falling-body viscometers, and vibrating [[Rheometer|viscometers]].<ref name="Measurement of the Transport Properties of Fluids review on Amazon">[http://www.amazon.com/dp/0632029978 Measurement of the Transport Properties of Fluids review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Solution Calorimetry\'\'\'\'\'
|
\'\'Solution Calorimetry\'\' is a book that gives background information on [[thermal analysis]] and [[calorimetry]]. Thermoanalytical and calorimetric techniques along with thermodynamic and kinetic properties are discussed in this book. Later volumes of this book discusses the applications and principles of these thermodynamic and kinetic methods.<ref name="Solution Calorimetry review on Amazon">[http://www.amazon.com/dp/044482085X Solution Calorimetry review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Equations of State for Fluids and Fluid Mixtures Part I\'\'\'\'\'
|
\'\'Equations of State for Fluids and Fluid Mixtures Part I\'\' is a book that gives up to date equations of state for fluids and fluid mixtures. This book covers all ways to develop equations of state. It gives the strengths and weaknesses of each equation. Some equations discussed include: [[virial]] equation of state cubic equations; generalized [[van der Waals]] Equations; integral equations; perturbation theory; and stating and mixing rules. Other things that \'\'Equations of State for Fluids and Fluid Mixtures Part I\'\' goes over are: associating fluids, polymer systems, polydisperse fluids, self-assembled systems, ionic fluids, and fluids near their critical points.<ref name="Equations of State for Fluids and Fluid Mixtures Part I review on Amazon">[http://www.amazon.com/dp/0444503846 Equations of State for Fluids and Fluid Mixtures part I review on Amazon] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Measurement of the Thermodynamic Properties of Single Phases\'\'\'\'\'
|
\'\'Measurement of the Thermodynamic Properties of Single Phases\'\' is a book that gives an overview of techniques for measuring the thermodynamic quantities of single phases. It also goes into experimental techniques to test many different [[thermodynamic state]]s precisely and accurately. \'\'Measurement of the Thermodynamic Properties of Single Phases\'\' was written for people interested in measuring thermodynamic properties.<ref name="Flipkart review of Measurement of the Thermodynamic Properties of Single Phases">[http://www.flipkart.com/book/measurement-thermodynamic-properties-single-phases/0444509313 Flipkart review of Measurement of the Thermodynamic properties of Single Phases] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Measurement of the Thermodynamic Properties of Multiple Phases\'\'\'\'\'
|
\'\'Measurement of the Thermodynamic Properties of Multiple Phases\'\' is a book that includes multiple techniques that are used to study multiple phases of pure component systems. Also included in this book are the measurement techniques to obtain activity [[coefficients]], [[interfacial tension]], and [[Parameter|critical parameters]]. This book was written for researchers and graduate students as a reference source.<ref name="Measurement of the Thermodynamic Properties of Multiple Phases review on Amazon">[http://www.amazon.com/dp/0444519777 Measurement of the Thermodynamic Properties of Multiple Phases review on Amazon] retrieved 15 April 2010</ref>
|}

===Series of books on analytical and physical chemistry of environmental systems===
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Atmospheric Particles\'\'\'\'\'
|
\'\'Atmospheric Particles\'\' is a book that delves into aerosol science. This book is aimed as a reference for graduate students and atmospheric researchers. \'\'Atmospheric Particles\'\' goes in depth on the properties of aerosols in the atmosphere and their effect. Topics covered in this book are: [[acid rain]]; [[heavy metals|heavy metal]] pollution; [[global warming]]; and [[photochemical]] smog. \'\'Atmospheric Particles\'\' also covers techniques to analyze the atmosphere and ways to take atmospheric samples.<ref name="Atmospheric Particles review">[http://www.flipkart.com/book/atmospheric-particles-harrison-roy-rene/0471959359 Flipkart review of Atmospheric Particles] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\'\'\'\'
|
\'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\' is a book that discusses environmental [[colloid]]s and current information available on them. This book focuses on environmental colloids and particles in aquatic systems and soils. It also goes over techniques such as: techniques for sampling environmental colloids, size fractionation, and how to characterize of colloids and particles. \'\'Environmental Colloids and Particles: Behaviour, Separation and Characterisation\'\' also delves into how these [[colloid]]s and [[particle]]s interact.<ref name="Environmental Colloids and Particles: Behaviour, Separation and Characterisation review on amazon">[http://www.amazon.com/dp/0470024321 Amazon Review of Environmental Colloids and Particles: Behaviour, Separation, and Characterisation] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\'\'\'\'
|
\'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\' is meant to give an overview of a technique based on [[fractal geometry]] and the processes of environmental systems. This book gives ideas on how to use [[fractal geometry]] to compare and contrast different [[ecosystems]]. It also gives an overview of the knowledge needed to solve environmental problems. Finally, \'\'Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems\'\' shows how to use the fractal approach to understand the reactivity of [[floc]]s, sediments, soils, microorganisms and [[humic]] substances.<ref name="Wiley page on Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems">[http://www.wiley.com/WileyCDA/WileyTitle/productCd-0470014741.html Wiley on Biophysical Chemistry of Fractal Structures and Processes in Environmental Systems]. New York: Wiley. Retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\'\'\'\'
|
\'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' is meant to be read by chemists and biologists that study environmental systems. Also, this book should be used as a reference for earth scientists, environmental geologists, environmental engineers, and professionals in microbiology and ecology. \'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' is about how minerals, microorganisms, and organic components work together to affect [[terrestrial ecoregion|terrestrial systems]]. This book identifies that there are many different techniques and theories about minerals, microorganisms, and organic components individually, but they aren\'t often associated with each other. It further goes on to discuss how these components of soil work together to affect [[Terrestrial animal|terrestrial]] life. \'\'Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem\'\' gives techniques to analyze minerals, microorganisms, and organic components together. This book also gives a large sections on why environmental scientists working in the specific fields of minerals, microorganisms, and organic components of soil should work together and how they should do so.<ref name="Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem review">[http://www.flipkart.com/book/interactions-between-soil-particles-microorganisms/0471607908 Flipkart review of Interactions Between Soil Particles and Microorganisms: Impact on the Terrestrial Ecosystem]. Retrieved 15 April 2010.</ref>
|-
| \'\'\'\'\'The Biogeochemistry of Iron in Seawater\'\'\'\'\'
|
\'\'The Biogeochemistry of Iron in Seawater\'\' is a book that describes how low concentrations of iron in [[Antarctica]] and the Pacific Oceans are a result of reduced chlorophyll for phytoplankton production.<ref name="SciTech Book News">SciTech Book News, Vol. 26, No. 2, June 2002.</ref> It does this by reviewing information from research in the 1990s. This book goes in depth about: chemical speciation; analytical techniques; transformation of iron; how iron limits the development of High Nutrient Low [[Chlorophyll]] areas in the [[pacific ocean]]<ref name="Amazon review of The Biogeochemistry of Iron in Seawater">[http://www.amazon.com/dp/0471490687 Review of Biogeochemistry of Iron in Seawater] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\'\'\'\'
|
\'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\' is a book that discusses techniques and devices to monitor [[marine ecoregion|aquatic systems]] and how new devices and techniques can be developed. This book emphasizes the future us of micro-analytical monitoring techniques and [[microtechnology]]. \'\'In Situ Monitoring of Aquatic Systems: Chemical Analysis and Speciation\'\' is aimed at researchers and laboratories that analyze aquatic systems such as rivers, lakes, and oceans.<ref name="review of Insitu Monitoring of Aquatic Systems: Chemical Analysis and Speciation review">[http://search.barnesandnoble.com/In-Situ-Monitoring-of-Aquatic-Systems/Jacques-Buffle/e/9780471489795/ Review of \'\'In Situ\'\' Monitoring of Aquatic Systems: Chemical Analysis and Speciation from Barnes and Noble]. Retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Structure and Surface Reactions of Soil Particles\'\'\'\'\'
|
\'\'Structure and Surface Reactions of Soil Particles\'\' is a book about soil structures and the molecular processes that occur in soil. \'\'Structure and Surface Reactions of Soil Particles\'\' is aimed at any researcher researching soil or someone in the field of [[anthropology]]. It goes in depth on topics such as: fractal analysis of particle dimensions; computer modeling of the structure; reactivity of humics; applications of atomic force microscopy; and advanced instrumentation for analysis of soil particles.<ref name="Review of Structure and Surface Reactions of Soil Particles">[http://www.lebooks.in/books/structure-surface-reactions-soil-particles-pan-ming-huang-f605dd78c7/9780471959366 Review of Structure and Surface Reactions of Soil Particles] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Metal [[Speciation]] and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\'\'\'\'
|
\'\'Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\' is a book about the effect of [[trace metals]] on aquatic life.<ref name="Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3 review">[http://www.amazon.com/dp/0471958301 Metal Speciation and Bioavailability in Aquatic Systems]. Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3. Review on Amazon. Retrieved 15 April 2010</ref> This book is considered a specialty book for researchers interested in observing the effect of [[trace metals]] in the water supply. This book includes techniques to assess how [[bioassays]] can be used to evaluate how an [[organism]] is affected by [[trace metals]]. Also, \'\'Metal Speciation and Bioavailability in Aquatic Systems, Series on Analytical and Physical Chemistry of Environmental Systems Vol. 3\'\' looks at the limitations of the use of bioassays to observe the effects of [[trace metals]] on organisms.
|-
| \'\'\'\'\'Physicochemical Kinetics and Transport at Biointerfaces\'\'\'\'\'
|
\'\'Physicochemical Kinetics and Transport at Biointerfaces\'\' is a book created to aid [[environmental scientist]]s in field work. The book gives an overview of chemical mechanisms, transport, kinetics, and interactions that occur in [[Environment (biophysical)|environmental systems]]. \'\'Physicochemical Kinetics and Transport at Biointerfaces continues from where \'\'Metal Speciation and Bioavailability in Aquatic Systems\'\' leaves off.<ref name="Physicochemical Kinetics and Transport at Biointerfaces review">[http://www.amazon.com/dp/0471498459 Physicochemical Kinetics and Transport at Biointerfaces review] retrieved 15 April 2010</ref>
|}

===Colored cover book and website series (nomenclature)===
IUPAC color codes their books in order to make each book distinguishable. Books that follow this trend are: [[Compendium of Analytical Nomenclature]]; [[Pure and Applied Chemistry]](journal); and [[Compendium of Chemical Terminology]].<ref name="History of IUPAC"/>
{| class="wikitable"
|-
! Book Name
! Description
|-
| \'\'\'\'\'Compendium of Analytical Nomenclature\'\'\'\'\'
|
One extensive book on almost all nomenclature written ([[IUPAC nomenclature of organic chemistry]] and [[IUPAC nomenclature of inorganic chemistry]]) by the IUPAC committee is [[Compendium of Analytical Nomenclature]] - The Orange Book, 1st edition (1978)<ref name="orange book">[http://www.iupac.org/objID/Source/sou89661252938339267969131 IUPAC orange book publication history]</ref> This book was revised in 1987. The second edition has many revisions that come from reports on nomenclature between 1976 and 1984.<ref name="orange book preamble">[http://old.iupac.org/publications/analytical_compendium/ Orange Book Preamble] retrieved 15 April 2010</ref> In 1992, the second edition went through many different revisions which led to the third edition.<ref name="orange book preamble">[http://old.iupac.org/publications/analytical_compendium/ Orange Book Preamble] retrieved 15 April 2010</ref>
|-
| \'\'\'\'\'Pure and Applied Chemistry\'\'\'\'\'
|
[[Pure and Applied Chemistry]] is the official monthly journal of IUPAC. This journal first debuted in 1960. The goal statement for [[Pure and Applied Chemistry]] is to "publish highly topical and credible works at the forefront of all aspects of pure and applied chemistry."<ref name="offical journal">[http://www.iupac.org/publications/pac/ IUPAC Pure and Applied Chemistry] retrieved 15 April 2010</ref> The Journal itself is available by subscription, but older issues are available in the archive on the IUPAC website.

[[Pure and Applied Chemistry]] was created as a central way to publish IUPAC endorsed articles.<ref name="First journal">[http://media.iupac.org/publications/pac/1960/pdf/0101x0003.pdf IUPAC Pure and Applied Chemistry Issue 1] retrieved 15 April 2010</ref> Before its creation, IUPAC didn\'t have a quick, official way to distribute new chemistry information.

Its creation was first suggested at The Paris IUPAC Meeting of 1957.<ref name="First journal">[http://media.iupac.org/publications/pac/1960/pdf/0101x0003.pdf IUPAC Pure and Applied Chemistry Issue 1] retrieved 15 April 2010</ref> During this meeting the commercial publisher of the Journal was discussed and decided on. In 1959, the IUPAC Pure and Applied Chemistry Editorial Advisory Board was created put in charge of the journal. The idea of one journal being a definitive place for a vast amount of chemistry was difficult for the committee to grasp at first.<ref name="First journal"/> However, it was decided that the journal would reprint old journal editions to keep all chemistry knowledge available.
|-
| \'\'\'\'\'Compendium of Chemical Terminology\'\'\'\'\'
|
The [[Compendium of Chemical Terminology]], also known as The Gold Book, was originally worked on by [[Victor Gold]]. This book is a collection of names and terms already discussed in [[Pure and Applied Chemistry]].<ref name="Gold Book">[http://goldbook.iupac.org/about.html Gold Book Online] retrieved 15 April 2010</ref> [[Compendium of Chemical Terminology]] was first published in 1987.<ref name="History of IUPAC"/> The first edition of this book contains no original material, but is meant to be a compilation of other IUPAC works.

The second edition of this book was published in 1997.<ref name="Gold Book second edition">[http://old.iupac.org/publications/compendium/] retrieved 15 April 2010</ref> This book made large changes to the first edition of The [[Compendium of Chemical Terminology]]. These changes included updated material and an expansion of the book to include over seven thousand terms.<ref name="Gold Book second edition">[http://old.iupac.org/publications/compendium/] retrieved 15 April 2010</ref> The second edition was the topic of an IUPAC [[XML]] project. This project made an [[XML]] version of the book that includes over seven thousand terms. The [[XML]] version of the book includes an open editing policy, which allows users to add excerpts of the written version.<ref name="Gold Book second edition"/>
|-
| \'\'\'\'\'IUPAC Nomenclature of Organic Chemistry (Online Publication)\'\'\'\'\'
| \'\'IUPAC Nomenclature of Organic Chemistry\'\' (publication), also known as The Blue Book, is a website published by Advanced Chemistry Department Incorporated with the permission of IUPAC. This site is a compilation of the books \'\'A Guide to IUPAC Nomenclature of Organic Compounds\'\' and \'\'Nomenclature of Organic Chemistry\'\'.<ref name="Blue Book">[http://www.acdlabs.com/iupac/nomenclature/ Online version of Blue Book] retrieved 15 April 2010</ref>
|}

==International Year of Chemistry==
[[Image:Internationalyearofchemistrylogo.png|thumb|upright|alt=A red square behind an orange square, which is behind a blue square that says "2011 C Chemistry" on it. Under this, there are the words "International Year of Chemistry 2011".|150px|International Year of Chemistry Logo|left| ]]

IUPAC and [[UNESCO]] are the lead organizations coordinating events for the [[International Year of Chemistry]], which will take place in 2011.<ref>[http://freitag.creighton.edu/OmahaACSfiles/N0848333.pdf United Nations Resolution 63/209: International Year of Chemistry.] February 3, 2009. Retrieved on April 24, 2010.</ref><ref name="note1">[http://www.chemistry2011.org/assets/42/IYC_prospectus.pdf About IYC: Introduction.] July 9, 2009. Retrieved on April 24, 2010.</ref> The [[International Year of Chemistry]] was originally proposed by IUPAC at the General Assembly in [[Turin]], [[Italy]].<ref name="International Year of Chemistry Prospectus">{{cite web|url=http://portal.acs.org/portal/PublicWebSite/membership/acs/getinvolved/CNBP_021696 |title=International Year of Chemistry Prospectus |publisher=Portal.acs.org |date= |accessdate=2011-06-08}}</ref> This motion was adopted by [[UNESCO]] at a meeting in 2008.<ref name="International Year of Chemistry Prospectus">[http://portal.acs.org/portal/PublicWebSite/membership/acs/getinvolved/CNBP_021696 International Year of Chemistry Prospectus] retrieved 15 April 2010</ref> The main objectives of the [[International Year of Chemistry]] is to increase public appreciation of chemistry and gain more interest in the world of [[chemistry]]. This event is also being held to encourage young people to get involved and contribute to [[chemistry]]. A further reason for this event being held is to honour how chemistry has made improvements to everyone\'s way of life.<ref name="IYC">[http://www.chemistry2011.org/about-iyc/introduction IYC: Introduction.] July 9, 2009. Retrieved on February 17, 2010. retrieved 15 April 2010</ref>

==Current projects==
===IUPAC current project list===
* Project Number 2009-012-2-200: [[Coordination polymers]] and [[metal organic framework]]s: terminology and nomenclature guidelines <ref name="IUPAC Current Projects">[http://www.iupac.org/indexes/Projects/years/2009 IUPAC Current Projects.] May 09, 2010. Retrieved on May 09, 2010.</ref>
** The objectives of this project are (1) to produce guidelines for terminology (glossary of terms) and [[nomenclature]] (concerning [[topology]], not naming of individual substances) in the area of [[coordination polymers]], (2) to ensure that these guidelines are accepted by a large group of leading researchers in the field, and (3) to have these guidelines implemented or referred to in the instructions to authors of leading general and [[inorganic chemistry]] journals.<ref name="CP and MOF Project">[http://www.iupac.org/web/ins/2009-012-2-200 CP and MOF Project.] May 09, 2010. Retrieved on May 09, 2010.</ref>
* Project Number 2009-032-1-100: Categorizing Halogen [[Chemical bond|Bonding]] and Other [[Noncovalent]] Interactions Involving [[Halogen]] Atoms<ref name="IUPAC Current Projects">[http://www.iupac.org/indexes/Projects/years/2010 IUPAC Current Projects.] February 15, 2010. Retrieved on February 17, 2010.</ref>
** The objective of this project is to give a modern definition to the term [[halogen]] bonding and to examine and classify [[halogens]] as [[electrophilic]] species and their [[intermolecular]] interactions.<ref name="Halogen Bonding Project">[http://www.iupac.org/web/ins/2009-032-1-100 Halogen Bonding Project.] February 15, 2010. Retrieved on February 17, 2010.</ref>
* Project Number 2009-048-1-600: Guidance for substance-related environmental monitoring strategies regarding soil and surface water<ref name="IUPAC Current Projects"/>
** The objective of this project is to identify new pollutants and their hazards and to monitor less investigated pollutants. Also, this project will provide strategies for how pollutants should be monitored. The advantages and disadvantages of each monitoring technique will be discussed.<ref name="Pollutant Project">[http://www.iupac.org/web/ins/2009-048-1-600 IUPAC Current Projects.] February 15, 2010. Retrieved on March 2, 2010. retrieved 15 April 2010</ref>
* Project Number 2009-034-2-700: [[Risk Assessment]] of Effects of [[Cadmium]] on Human Health<ref name="IUPAC Current Projects"/>
** The objective of this project is to identify the risks and effects of exposure of humans to [[Cadmium]], which is classified as a carcinogenic to humans by the International Agency for Research on [[Cancer]]. Also, the objective includes researching how [[Cadmium]] enters into the human body.<ref name="Cadium">[http://www.iupac.org/web/ins/2009-034-2-700 IUPAC Current Projects.] February 15, 2010. Retrieved on March 2, 2010.</ref>
* Project Number 2009-019-2-400: Data Treatment in SEC and Other Techniques of [[Polymer]] Characterization. Correction for Band Broadening and Other Sources of Error.<ref name="IUPAC Current Projects"/>
** The objective of this project is to provide practical alternatives for improving the accuracy of [[polymer]] characterization and measurements. This would allow manufacturers of equipment, such as [[Size exclusion chromatography|Size exclusive chromatography]] (SEC) and other [[polymer]] characterization techniques, to sell a product that is more accurate and precise.<ref name="IUPAC Current Projects"/>

==See also==
* [[CAS registry number]]
* [[Element naming controversy]]
* [[International Union of Biochemistry and Molecular Biology]] (IUBMB)
* [[International Chemical Identifier]] (InChI)
* [[Institute for Reference Materials and Measurements]] (IRMM)
* [[National Institute of Standards and Technology]] (NIST)
* [[International Union of Pure and Applied Chemistry nomenclature]]
* [[European Association for Chemical and Molecular Sciences]]

==References==
{{Reflist|colwidth=30em}}

==External links==
* {{official|http://www.iupac.org}}
*{{cite web|url=http://www.chem.qmul.ac.uk/iubmb/thermod/|title=Recommendations for nomenclature and tables in biochemical thermodynamics|author=Panel on Biochemical Thermodynamics|year=1994|publisher=G. P. Moss, [[Queen Mary University of London]]}}
{{Good article}}

{{International Council for Science}}

{{DEFAULTSORT:International Union Of Pure And Applied Chemistry}}
[[Category:Chemistry organizations]]
[[Category:International scientific organizations]]
[[Category:Standards organizations]]
[[Category:Chemical nomenclature]]
[[Category:Organizations established in 1919]]

[[af:IUPAC]]
[[ar:الاتحاد الدولي للكيمياء البحتة والتطبيقية]]
[[az:Beynəlxalq Nəzəri və Tətbiqi Kimya İttifaqı]]
[[be:IUPAC]]
[[bs:IUPAC]]
[[br:Unaniezh Etrevroadel ar Gimiezh Pur hag Arveret]]
[[bg:Международен съюз за чиста и приложна химия]]
[[ca:Unió Internacional de Química Pura i Aplicada]]
[[cs:Mezinárodní unie pro čistou a užitou chemii]]
[[cy:IUPAC]]
[[da:International Union of Pure and Applied Chemistry]]
[[de:International Union of Pure and Applied Chemistry]]
[[el:Διεθνής Ένωση Καθαρής και Εφαρμοσμένης Χημείας]]
[[es:Unión Internacional de Química Pura y Aplicada]]
[[eo:IUPAK]]
[[fa:اتحادیه بین‌المللی شیمی محض و کاربردی]]
[[fr:Union internationale de chimie pure et appliquée]]
[[gl:IUPAC]]
[[ko:국제순수·응용화학연합]]
[[hi:शुद्ध और अनुप्रयोगिक रसायन का अंतरराष्ट्रीय संघ]]
[[hr:Međunarodna unija za čistu i primijenjenu kemiju]]
[[id:International Union of Pure and Applied Chemistry]]
[[it:IUPAC]]
[[ka:თეორიული და გამოყენებითი ქიმიის საერთაშორისო კავშირი]]
[[lb:International Union of Pure and Applied Chemistry]]
[[lmo:IUPAC]]
[[hu:IUPAC]]
[[mk:Меѓународна унија за чиста и применета хемија]]
[[ml:ഇന്റർനാഷണൽ യൂണിയൻ ഓഫ് പ്യുർ ആന്റ് അപ്ലൈഡ് കെമിസ്ട്രി]]
[[ms:IUPAC]]
[[nl:IUPAC]]
[[ja:国際純正・応用化学連合]]
[[no:IUPAC]]
[[nds:International Union of Pure and Applied Chemistry]]
[[pl:Międzynarodowa Unia Chemii Czystej i Stosowanej]]
[[pt:União Internacional de Química Pura e Aplicada]]
[[ro:Uniunea Internațională de Chimie Pură și Aplicată]]
[[ru:Международный союз теоретической и прикладной химии]]
[[sq:IUPAC]]
[[simple:IUPAC]]
[[sk:Medzinárodná únia čistej a aplikovanej chémie]]
[[sl:Mednarodna zveza za čisto in uporabno kemijo]]
[[sr:Међународна унија за чисту и примењену хемију]]
[[sh:IUPAC]]
[[fi:IUPAC]]
[[sv:International Union of Pure and Applied Chemistry]]
[[ta:தனி மற்றும் பயன்பாட்டு வேதியியல் அனைத்துலக ஒன்றியம்]]
[[tr:Uluslararası Temel ve Uygulamalı Kimya Birliği]]
[[uk:IUPAC]]
[[ur:بین الاقوامی اتحاد براۓ خالص و نفاذی کیمیاء]]
[[vi:IUPAC]]
[[wuu:优八克]]
[[zh:國際純粹與應用化學聯合會]]"""
h=hasher.update(text)
hasher.digest()
''', setup_stmt)
res = t.timeit()
print 'Processing time md5 hash: %s' % res
