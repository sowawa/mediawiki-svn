source("util/env.R")



load_reverter_months = function(verbose=T, reload=F){
	filename = paste(DATA_DIR, "en.reverter_months.20110115.tsv", sep="/")
	if(is.null(REVERTER_MONTHS) | reload){
		REVERTER_MONTHS <<- NULL
	}
	if(is.null(REVERTER_MONTHS)){
		if(verbose){cat("Loading reverter months from", filename, "...")} 
		REVERTER_MONTHS <<- read.table(
			filename, 
			header=T, sep="\t", 
			quote="'\"", comment.char="", 
			na.strings="\\N",
		)
		if(verbose){cat("DONE!\n")}
	}
	REVERTER_MONTHS
}


