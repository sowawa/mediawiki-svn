import argparse, sys, os



def main(args):
	files = {
		'revert': args.revert,
		'reverted': args.reverted
	}
	
	for line in args.input:
		ty = eval(line.strip().split("\t")[0])
		files[ty].write(line.split("\t", 1)[1])
		
		if ty == "revert":
			sys.stderr.write("<")
		elif ty == "reverted":
			sys.stderr.write("|")
		
	sys.stderr.write("\n")
		
	
if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Cleans revert data from a dump map process.'
	)
	parser.add_argument(
		'-i', '--input',
		metavar="<path>",
		type=lambda fn:open(fn, "r"), 
		help='the path of the file to filter (defaults to stdin)',
		default=sys.stdin
	)
	parser.add_argument(
		'--reverted',
		metavar="<path>",
		type=lambda fn:open(os.path.expanduser(fn), "w"), 
		help='the path to a file to produce representing the reverted revisions'
	)
	parser.add_argument(
		'--revert',
		metavar="<path>",
		type=lambda fn:open(os.path.expanduser(fn), "w"), 
		help='the path to a file to produce representing the reverting revisions'
	)
	args = parser.parse_args()
	main(args)

