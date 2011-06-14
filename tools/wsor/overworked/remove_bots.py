import sys, argparse


def main(args):
	
	bots = set()
	for line in args.bots:
		bots.add(int(line.strip()))
	
	headerLine = args.input.readline().strip()
	headers = [eval(h) for h in headerLine.split("\t")]
	print(headerLine)
	
	for line in args.input:
		row = dict(zip(headers, [eval(v) for v in line.strip().split("\t")]))
		if row['user_id'] not in bots:
			print(line.strip())
		
	


if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description=
			'Removes bot editors from patrollers file'
	)
	parser.add_argument(
		'bots',
		metavar="<path>",
		type=lambda fn:open(fn, "r"), 
		help='the path to the bots file'
	)
	parser.add_argument(
		'-i', '--input',
		metavar="<path>",
		type=lambda fn:open(fn, "r"), 
		help='the path of the file to filter (defaults to stdin)',
		default=sys.stdin
	)
	args = parser.parse_args()
	main(args)
