import sys, subprocess, os
from gl import wp

class FileTypeError(Exception):pass
class FileTypeWarning(Warning):pass

EXTENSIONS = {
	'xml': "cat",
	'bz2': "bzcat"
	'7z':  "7z e -so"
}

EXT_RE = re.compile(r'\.(.+)')
def dumpFile(fn):
	match = EXT_RE.find(fn)
	if match == None:
		raise FileTypeError("No extension found for %s." % fn)
	elif match.groups(1) not in EXTENSIONS:
		raise FileTypeError("File type %s for %s is not supported." % (match.groups(1), fn))
	else:
		call = EXTENSIONS[match.groups(1)]
		process = subprocess.Popen("%s %s" % (call, fn), shell=True, stdout=subprocess.PIPE)
		return wp.dump.Iterator(process.stdout)






if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Extracts identity reverts and diff information from dump file(s)'
	)
	parser.add_argument(
		'dumpFile',
		metavar="<path>",
		type=dumpFile, 
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
