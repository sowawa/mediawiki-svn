source("loader/load_patroller_days.R")

patroller_days = load_patroller_days()
patroller_days = patroller_days[!grepl("bot( |$)", patroller_days$username, ignore.case=T),]
patroller_days = patroller_days[!grepl("DASHBot", patroller_days$username, ignore.case=T),]

library(lattice)
library(doBy)


patroller_years = with(
	summaryBy(
		count ~ year + user_id + username, 
		data=patroller_days,
		FUN=sum
	),
	data.frame(
		year = year,
		user_id = user_id,
		username = username,
		count = count.sum
	)
)

patroller_years = patroller_years[order(patroller_years$count),]
patroller_years$count_bucket = 2^round(log(patroller_years$count, base=2))

patroller_years.count_dist = with(
	summaryBy(
		user_id ~ year + count,
		data = patroller_years,
		FUN=length
	),
	data.frame(
		year = year,
		count = count,
		freq = user_id.length
	)
)

png('plots/dist.patroller_years_activity.png', height=768, width=1024)
xyplot(
	freq ~ count | as.character(year),
	data = patroller_years.count_dist,
	panel = function(x, y, subscripts, group, ...){
		panel.xyplot(x, y)
		panel.lines(x, y)
	},
	main="Distribution of activity level among editors",
	ylab="Frequency",
	xlab="Activity level",
	#scales=list(
	#	x=list(
	#		log=2, 
	#		at=2^(1:max(patroller_years.count_dist$count)), 
	#		labels=2^(1:max(patroller_years.count_dist$count))
	#	)
	#),
	layout=c(length(unique(patroller_years.count_dist$year)), 1)
)
dev.off()



for(year in sort(unique(patroller_years$year))){
	p_year = patroller_years[patroller_years$year==year,]
	p_year = p_year[order(p_year$count, decreasing=T),]
	png(paste('plots/bars.patroller_years_activity', year, 'png', sep="."), height=768, width=1024)
	print(barchart(
		reorder(substring(as.character(username),1,30), count) ~ count,
		data=p_year[1:50,],
		horizontal=T,
		xlim=c(0, 110000),
		xlab="Patrolled pages"
	))
	dev.off()
	cat(year, "\n")
	print(summary(p_year$count))
}


patroller_months = with(
	summaryBy(
		count ~ year + month + user_id + username, 
		data=patroller_days,
		FUN=sum
	),
	data.frame(
		year = year,
		month = month,
		user_id = user_id,
		username = username,
		count = count.sum
	)
)

nNoNA = function(x){
	length(subset(x, !is.na(x)))
}
sdNoNA = function(x){
	sd(x, na.rm=T)/sqrt(nNoNA(x))	
}
meanNoNA = function(x){
	mean(x, na.rm=T)
}

patrol_months.per_user = with(
	summaryBy(
		count ~ year + month,
		data=patroller_months,
		FUN=c(meanNoNA, sdNoNA, nNoNA)
	),
	data.frame(
		year        = year,
		month       = month,
		year.month  = year + month/100,
		count.mean  = count.meanNoNA,
		count.sd    = count.sdNoNA,
		count.n     = count.nNoNA
	)
)

model = lm(
	count.mean ~ as.numeric(factor(year.month)),
	data=patrol_months.per_user[patrol_months.per_user$year.month <= 2011.05,]
)
summary(model)
monthLine = function(x){
	model$coefficients[['(Intercept)']] + model$coefficients[['as.numeric(factor(year.month))']]*x
}

png("plots/patrol_months.per_user.png", height=768, width=1024)
print(xyplot(
	count.mean ~ as.factor(year.month),
	data = patrol_months.per_user[patrol_months.per_user$year.month <= 2011.05,],
	panel = function(x, y, subscripts, ...){
		f = patrol_months.per_user[patrol_months.per_user$year.month <= 2011.05,][subscripts,]
		panel.xyplot(x, y, col="#000000", ...)
		se = f$count.sd/sqrt(f$count.n)
		panel.arrows(x, y+se, x, y-se, ends="both", angle=90, col="#000000", length=0.05, ...)
		panel.lines(x[order(x)], y[order(x)], lwd=2, ...)
		panel.lines(x[order(x)], monthLine(as.numeric(x[order(x)])), lwd=2, col="#000000")
	},
	#main="Average Patroller workload by month",
	ylab="Mean patrolled pages per user",
	xlab="Month",
	scales=list(x=list(rot=45))
))
dev.off()

patrol_years.per_user = with(
	summaryBy(
		count ~ year,
		data=patroller_years,
		FUN=c(meanNoNA, sdNoNA, nNoNA)
	),
	data.frame(
		year        = year,
		count.mean  = count.meanNoNA,
		count.sd    = count.sdNoNA,
		count.n     = count.nNoNA
	)
)

model = lm(
	count.mean ~ year,
	data=patrol_years.per_user[patrol_years.per_user$year <= 2010,]
)
summary(model)

model = lm(
	count.mean ~ log(year-2006, base=2),
	data=patrol_years.per_user[patrol_years.per_user$year <= 2010,]
)
summary(model)
yearCurve=function(x){
	model$coefficients[['(Intercept)']] + log(x-2006, base=2)*model$coefficients[['log(year - 2006, base = 2)']]
}
png("plots/patrol_years.per_user.png", height=768, width=1024)
print(xyplot(
	count.mean ~ year-2006,
	data = patrol_years.per_user[patrol_years.per_user$year <= 2010,],
	panel = function(x, y, subscripts, ...){
		f = patrol_years.per_user[patrol_years.per_user$year.month <= 2011.05,][subscripts,]
		panel.xyplot(x, y, col="#000000", ...)
		se = f$count.sd/sqrt(f$count.n)
		panel.arrows(x, y+se, x, y-se, ends="both", angle=90, col="#000000", length=0.05, ...)
		#panel.lines(x[order(x)], y[order(x)], lwd=2, ...)
		#panel.curve(myCurve, 2006, 2011, col="#000000")
		panel.lines(seq(0, 5, .1), yearCurve(seq(2006, 2011, .1)), lwd=2, col="#000000")
	},
	#main="Average Patroller workload by year",
	ylab="Mean patrolled pages per user",
	xlab="Year (log scaled)",
	pch=20,
	scales=list(x=list(at=1:5, labels=2007:2010))
))
dev.off()

