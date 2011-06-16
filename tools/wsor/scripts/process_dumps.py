import sys, logging, re, types, argparse, os
from multiprocessing import Process, Queue, Lock, cpu_count
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
	
	def __init__(self, fileQueue, process, output, logger):
		self.fileQueue = fileQueue
		self.process   = process
		self.output    = output
		self.logger    = logger
		Process.__init__(self)
	
	def start(self):
		try:
			while True:
				fn = self.fileQueue.get(block=False)
				self.logger.info("Processing dump file %s." % fn)
				dump = wp.dump.Iterator(fn)
				for page in dump:
					self.logger.debug("Processing dump file %s." % fn)
					try:
						self.process(page, output)
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
		except Exception as e:
			raise e


def main(args):
	LOGGING_STREAM = sys.stderr
	if __debug__: level = logging.DEBUG
	else:         level = logging.INFO
	logging.basicConfig(
		level=level,
		stream=LOGGING_STREAM,
		format='%(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	logging.info("Starting dump processor with %s threads." % args.threads)
	
	dumpQueue = dumpFiles(args.dump)
	output = SafeOutput(args.out)
	processors = []
	for i in range(0, min(args.threads, len(args.dump))):
		p = Processor(
			dumpQueue, 
			args.processor.process, 
			output, 
			logging.getLogger("Process %s" % i)
		)
		processors.append(p)
		
	try:
		for i in processors:
			processor.join()
		
	except KeyboardInterrupt:
		logging
		
	


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
		return path

def dumpFiles(paths):
	q = Queue()
	for path in paths: q.put(dumpFile(path))
	return q
	

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
	
