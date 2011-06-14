import pymongo, logging, time, argparse, sys, types
from collections import deque



def main(args):
	LOGGING_STREAM = sys.stderr
	logging.basicConfig(
		level=logging.DEBUG,
		stream=LOGGING_STREAM,
		format='%(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	
	logging.info("Connecting to mongo.")
	db = pymongo.Connection().wikilytics
	
	logging.info("Getting arbitration article ids.")
	catIds = set([
			a['id'] for a in 
			db.enwiki_articles_dataset.find({'category': args.category})
	])
	logging.info("Found %s articles in the '%s' category." % (len(catIds), args.category))
	
	#Printing headers
	print(
		"\t".join([
			'username',
			'user_id',
			'month',
			'year',
			'edits'
		])
	)
	def limitPeriod(period):
		def limit(item, l):
			return (
				time.mktime(item['date']) - 
				time.mktime(l[0]['date'])
			) < period
	
	for editor in db.enwiki_editors_raw.find():
		thresh = False
		for year, month, edits in get_months_of_edits(editor['edits']):
			catEdits = [e for e in edits if e['article'] in catIds]
			if len(catEdits) >= args.n:
				print(
					"\t".join(clean(v) for v in [
						editor['username'],
						editor['editor'],
						year,
						month,
						len(catEdits)
					])
				)
				thresh = True
			
		if thresh:
			LOGGING_STREAM.write("o")
		else:
			LOGGING_STREAM.write("-")
					
			
		
	

def clean(value):
	if value == None:
		return "\N"
	elif type(value) in types.StringTypes:
		return value.encode('utf-8').replace("\\", "\\\\").replace("\t", "\\t").replace("\n", "\\n")
	else:
		return str(value)

def get_months_of_edits(edits):
	for year, edits in edits.items():
		if len(edits) == 0: continue
		
		#set
		currMonth = edits[0]['date'].strftime("%m")
		monthEdits = []
		for edit in edits:
			month = edit['date'].strftime("%m")
			if month != currMonth:
				yield (year, month, monthEdits)
				
				#reset
				currMonth = month
				monthEdits = []
			
			monthEdits.append(edit)
		
		yield (year, month, monthEdits)
	

def capitalize(word):
	if len(word) < 1:
		return word
	else:
		return word[0].capitalize() + word[1:]



"""class LimQueue(list):
	
	def __init__(self, iterable=[], limit=lambda l, item: True):
		list.__init__(self, iterable)
		self.limit = limit
	
	def append(self, item):
		expectoration = []
		while not self.limit(self, item):
			expectoration.append(self.pop(0))
			
		return expectoration
"""	

if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Finds editors that made at least some number of ' + 
		            'edits to a category of articles in a month.  ' +
		            'This script prints one row for each editor-month ' + 
		            'with enough edits to a category of articles.'
	)
	parser.add_argument(
		'n',
		type=int, 
		help='the threshold number of edits per time period in a ' + 
		     'category for inclusion'
	)
	parser.add_argument(
		'category',
		type=capitalize,
		help='the category in which to search for edits'
	)
	args = parser.parse_args()
	main(args)

