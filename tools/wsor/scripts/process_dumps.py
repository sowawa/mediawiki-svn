import sys, logging, re, types, argparse, os, subprocess
from multiprocessing import Process, Queue, Lock, cpu_count, Value
from Queue import Empty
from gl import wp

class FileTypeError(Exception):pass

def encode(v):
	if type(v) == types.FloatType:
		return str(int(v))
	elif v == None:
		return "\\N"
	else:
		return repr(v)



class SafeOutput:
	
	def __init__(self, fp):
		self.fp = fp
		self.l  = Lock()
	
	def push(self, row, encode=encode):
		if __debug__:
			row = tuple(row)
		
		with self.l:
			self.fp.write("\t".join(clean(v) for v in row) + "\n")

class Processor(Process):
	
	def __init__(self, input, processPage, output, callback, logger):
		self.input       = input
		self.processPage = processPage
		self.output      = output
		self.callback    = callback
		self.logger      = logger
		Process.__init__(self)
	
	def run(self):
		try:
			while True:
				foo = self.input.qsize()
				fn = self.input.get(block=False)
				self.logger.info("Processing dump file %s." % fn)
				dump = wp.dump.Iterator(openDumpFile(fn))
				for page in dump.readPages():
					self.logger.debug("Processing page %s:%s." % (page.getId(), page.getTitle()))
					try:
						for out in self.processPage(dump, page):
							self.output.put(out)
					except Exception as e:
						self.logger.error(
							"Failed to process page %s:%s - %s" % (
								page.getId(),
								page.getTitle(),
								e
							)
						)
					
				
			
			
		except Empty:
			self.logger.info("Nothing left to do.  Shutting down thread.")
		finally:
			self.callback()
		
	


def main(args):
	LOGGING_STREAM = sys.stderr
	if __debug__: level = logging.DEBUG
	else:         level = logging.INFO
	logging.basicConfig(
		level=level,
		stream=LOGGING_STREAM,
		format='%(name)s: %(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	logging.info("Starting dump processor with %s threads." % min(args.threads, len(args.dump)))
	for row in process_dumps(args.dump, args.processor.process, args.threads):
		print('\t'.join(encode(v) for v in row))

def process_dumps(dumps, processPage, threads):
	input       = dumpFiles(dumps)
	output      = Queue(maxsize=10000)
	running     = Value('i', 0)
	
	def dec(): running.value -= 1
	
	for i in range(0, min(threads, input.qsize())):
		running.value += 1
		Processor(
			input, 
			processPage,
			output, 
			dec,
			logging.getLogger("Process %s" % i)
		).start()
	
	
	#output while processes are running
	while running.value > 0:
		try:          yield output.get(timeout=.25)
		except Empty: pass
	
	#finish yielding output buffer
	try:
		while True: yield output.get(block=False) 
	except Empty: 
		pass
	


EXTENSIONS = {
	'xml': "cat",
	'bz2': "bzcat",
	'7z':  "7z e -so 2>/dev/null",
	'lzma':"lzcat"
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
		return path

def dumpFiles(paths):
	q = Queue()
	for path in paths: q.put(dumpFile(path))
	return q

def openDumpFile(path):
	match = EXT_RE.search(path)
	ext = match.groups()[0]
	p = subprocess.Popen(
		"%s %s" % (EXTENSIONS[ext], path), 
		shell=True, 
		stdout=subprocess.PIPE
	)
	return p.stdout
	

if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Maps a function across pages of MediaWiki dump files'
	)
	parser.add_argument(
		'-o', '--out',
		metavar="<path>",
		type=lambda path:open(path, "w"), 
		help='the path to an output file to write putput to (defaults to stdout)',
		default=sys.stdout
	)
	parser.add_argument(
		'-t', '--threads',
		metavar="",
		type=int, 
		help='the number of threads to start (defaults to # of cores -1)',
		default=cpu_count()-1
	)
	parser.add_argument(
		'processor',
		type=__import__, 
		help='the class path to the function to use to process each page'
	)
	parser.add_argument(
		'dump',
		type=dumpFile, 
		help='the XML dump file(s) to process',
		nargs="+"
	)
	args = parser.parse_args()
	main(args)

