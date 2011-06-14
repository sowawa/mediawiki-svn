source("util/env.R")

PATROLLER_DAYS <<- NULL

load_patroller_days = function(verbose=T, reload=F){
	filename = paste(DATA_DIR, "en.patroller_days.20110610.no_bots.tsv", sep="/")
	PATROLLER_DAYS <<- read.table(
		filename, 
		header=T, sep="\t", 
		quote="'\"", comment.char="", 
		na.strings="\\N",
	)
	PATROLLER_DAYS
}
