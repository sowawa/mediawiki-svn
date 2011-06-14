import types, sys

def clean(v):
	if type(v) == types.LongType:
		return str(int(v))
	else:
		return repr(v)
		
	

f = open("patroller_days.20110610.tsv")
out = open("patroller_days.fixed.tsv", "w")

for line in f:
	vals = [eval(val) for val in line.strip().split("\t")]
	out.write("\t".join(clean(val) for val in vals))
	out.write("\n")

out.close()

	
