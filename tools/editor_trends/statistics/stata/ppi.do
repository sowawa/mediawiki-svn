clear
insheet using "C:\Users\diederik.vanliere\Desktop\ppi_quality.csv"
label var character_count_a "PPI editor"
label var character_count_b "Regular editor"

label var cum_edit_count_main_ns_a "PPI editor"
label var cum_edit_count_main_ns_b "Regular editor"

label var article_count_a "PPI editor"
label var article_count_b "Regular editor"

label var cum_edit_count_other_ns_a "PPI editor"
label var cum_edit_count_other_ns_b "Regular editor"

label var revert_count "PPI editor"
label var v14 "Regular editor"


local dataset_note = "Dataset: http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/analyses/adhoc/ppi_quality.py [REV: 88408]"
local chart_note = "Chart: http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/statistics/stata/ppi.do [REV: 88409]"
local copyright = "Chart is available under the Creative Commons Attribution/Share-Alike License."
local date ="Summer of Research / Wikimedia Foundation / $S_DATE"

graph bar (asis) character_count_a character_count_b, over(editor_a, sort(id) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Characters Added) legend(rows(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
graph export "ppi_character_count.png", replace
graph bar (asis) cum_edit_count_main_ns_a  cum_edit_count_main_ns_b, over(editor_a, sort(id) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Cumulative edits in main mamespace) legend(rows(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
graph export "ppi_cum_edit_count_main_ns.png", replace
graph bar (asis) article_count_a article_count_b, over(editor_a, sort(id) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Number of articles edited) legend(rows(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
graph export "ppi_article_count.png", replace
graph bar (asis) cum_edit_count_other_ns_a cum_edit_count_other_ns_b, over(editor_a, sort(id) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Cumulative edits in non main namespace) legend(rows(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
graph export "ppi_cum_edit_count_other_ns.png", replace
graph bar (asis) revert_count v14, over(editor_a, sort(id) label(nolabel)) title(Matching PPI editors and Regular editors) subtitle(Number of edits reverted) legend(rows(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
graph export "ppi_revert_count.png", replace


//graph bar (asis) character_count_a character_count_b, over(editor_a, sort(character_count_a) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Characters Added) legend(cols(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'", size(tiny))
//graph export ppi_character_count.png, replace
//graph bar (asis) cum_edit_count_main_ns_a  cum_edit_count_main_ns_b, over(editor_a, sort(cum_edit_count_main_ns_a) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Cumulative edits in main mamespace) legend(cols(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'", size(tiny))
//graph export "ppi_cum_edit_count_main_ns.png", replace
//graph bar (asis) article_count_a article_count_b, over(editor_a, sort(article_count_a) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Number of articles edited) legend(cols(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'", size(tiny))
//graph export "ppi_article_count.png", replace
//graph bar (asis) cum_edit_count_other_ns_a cum_edit_count_other_ns_b, over(editor_a, sort(cum_edit_count_other_ns_a) descending label(nolabel)) nofill title(Matching PPI and Regular Editors) subtitle(Cumulative edits in non main namespace) legend(cols(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'", size(tiny))
//graph export "ppi_cum_edit_count_other_ns.png", replace
//graph bar (asis) revert_count v14, over(editor_a, sort(revert_count) label(nolabel)) title(Matching PPI editors and Regular editors) subtitle(Number of edits reverted) legend(cols(1) size(small)) note("`chart_note'" "`dataset_note'" "`copyright'" "`date'", size(tiny))
//graph export "ppi_revert_count.png", replace
