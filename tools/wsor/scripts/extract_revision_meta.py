import sys, subprocess, os, errno, re, argparse, logging, hashlib, types
from difflib import SequenceMatcher
from gl import wp
from gl.containers import LimitedDictLists

from text import STOP_WORDS, MARKUP

class FileTypeError(Exception):pass
class FileTypeWarning(Warning):pass

def clean(v):
	if type(v) == types.LongType:
		return str(int(v))
	elif v == None:
		return "\\N"
	else:
		return repr(v)


EXTENSIONS = {
	'xml': "cat",
	'bz2': "bzcat",
	'7z':  "7z e -so"
}

EXT_RE = re.compile(r'\.([^\.]+)$')
def dumpFile(path):
	path = os.path.expanduser(path)
	if not os.path.isfile(path):
		raise FileTypeError("Can't find file %s" % path)
	
	match = EXT_RE.search(path)
	if match == None:
		raise FileTypeError("No extension found for %s." % path)
	elif match.groups()[0] not in EXTENSIONS:
		raise FileTypeError("File type %r is not supported." % path)
	else:
		call = EXTENSIONS[match.groups()[0]]
		process = subprocess.Popen("%s %s" % (call, path), shell=True, stdout=subprocess.PIPE)
		return process.stdout


def output(path):
	path = os.path.expanduser(path)
	try:
		os.makedirs(path)
	except OSError as e:
		if e.errno == errno.EEXIST:
			return path
		else:
			raise e
		
	return path
	
def tokenize(text):
	return re.findall(
		r"[\w]+|\[\[|\]\]|\{\{|\}\}|\n+| +|&\w+;|'''|''|=+|\{\||\|\}|\|\-|.",
		text
	)

def simpleDiff(a, b):
	sm = SequenceMatcher(None, a, b)
	added = []
	removed = []
	for (tag, i1, i2, j1, j2) in sm.get_opcodes():
		if tag == 'replace':
			removed.extend(a[i1:i2])
			added.extend(b[j1:j2])
		elif tag == 'delete':
			removed.extend(a[i1:i2])
		elif tag == 'insert':
			added.extend(b[i1:i2])
		
	return (added, removed)



def main(args):
	LOGGING_STREAM = sys.stderr
	logging.basicConfig(
		level=logging.DEBUG,
		stream=LOGGING_STREAM,
		format='%(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	
	logging.info("Setting up output files in %s" % args.out)
	reverts  = open(os.path.join(args.out, "revert.tsv"), "w")
	revertHeaders = ['rev_id', 'to_id', 'revs_reverted']
	#reverts.write("\t".join(revertHeaders) + "\n")
		
	reverted = open(os.path.join(args.out, "revert.tsv"), "w")
	revertedHeaders = ['rev_id', 'rvtg_id', 'rvtto_id', 'revs_reverted']
	#reverted.write("\t".join(revertedHeaders) + "\n")
		
	meta     = open(os.path.join(args.out, "revert.tsv"), "w")
	metaHeaders = ['rev_id', 'checksum', 'tokens', 'cs_added', 'cs_removed', 'ts_added', 'ts_removed', 'ws_added', 'ws_removed', 'ms_added', 'ms_removed']
	#meta.write("\t".join(metaHeaders) + "\n")
	
	logging.info("Reading from dump file.")
	for page in wp.dump.Iterator(args.dump).readPages():
		logging.debug("Processing %s:%s..." % (page.getId(), page.getTitle()))
		recentRevs = LimitedDictLists(maxsize=15)
		lastTokens = []
		for revision in page.readRevisions():
			checksum = hashlib.md5(revision.getText().encode("utf-8")).hexdigest()
			if checksum in recentRevs:
				LOGGING_STREAM.write("r")
				#found a revert
				revertedToRev = recentRevs[checksum]
				
				#get the revisions that were reverted
				revertedRevs = [r for (c, r) in recentRevs if r.getId() > revertedToRev.getId()]
				
				#write revert row
				revert.write(
					"\t".join(clean(v) for v in [
						revision.getId(), 
						revertedToRev.getId(), 
						len(revertedRevs)
					]) + "\n"
				)
				
				LOGGING_STREAM.write(str(len(revertedRevs)))
				for rev in revertedRevs:
					reverted.write(
						"\t".join(clean(v) for v in [
							rev.getId(),
							revision.getId(),
							revertedToRev.getId(), 
							len(revertedRevs)
						]) + "\n"
					)
			else:
				LOGGING_STREAM.write("-")
				
			tokens = tokenize(revision.getText())
			
			tokensAdded, tokensRemoved = simpleDiff(lastTokens, tokens)
			
			row = {
				'rev_id':     revision.getId(),
				'checksum':   checksum,
				'tokens':     len(revision.getText()),
				'cs_added':   0,
				'cs_removed': 0,
				'ts_added':   0,
				'ts_removed': 0,
				'ws_added':   0,
				'ws_removed': 0,
				'ms_added':   0,
				'ms_removed': 0
			}
			for token in tokensAdded:
				row['ts_added'] += 1
				row['cs_added'] += len(token)
				if token.strip() == '':       pass
				if token in MARKUP:           row['ms_added'] += 1
				elif token not in STOP_WORDS: row['ws_added'] += 1
			for token in tokensRemoved:
				row['ts_removed'] += 1
				row['cs_removed'] += len(token)
				if token.strip() == '':       pass
				if token in MARKUP:           row['ms_removed'] += 1
				elif token not in STOP_WORDS: row['ws_removed'] += 1
				
			
			reverted.write(
				"\t".join([clean(row[h]) for h in metaHeaders]) + "\n"
			)
			
			lastTokens = tokens
		
		LOGGING_STREAM.write("\n")
			
		
	



if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Extracts identity reverts and diff information from dump file(s)'
	)
	parser.add_argument(
		'-d', '--dump',
		metavar="<path>",
		type=dumpFile, 
		help='the path to the XML dump file to process (defaults to stdin)',
		default=sys.stdin
	)
	parser.add_argument(
		'-o', '--out',
		metavar="<path>",
		type=output, 
		help='the path to a diectory to write output files/read previous files from',
		default="."
	)
	args = parser.parse_args()
	main(args)
