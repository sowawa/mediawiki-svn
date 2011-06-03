import os, sys, logging, argparse, MySQLdb, urllib2, urllib, json

def clean(v):
	if v == None:
		return "\N"
	else:
		return str(v).replace("\t", "\\t").replace("\n", "\\n").replace("\\", "\\\\")
	

def main(args):
	LOGGING_STREAM = sys.stderr
	logging.basicConfig(
		level=logging.DEBUG,
		stream=LOGGING_STREAM,
		format='%(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	
	logging.info("Reading from %s." % args.input)
	
	#Print header
	print(
		"\t".join([
			'rev_id',
			'diff'
		])
	)
	
	rowBuffer = []
	for row in readTSVFile(args.input):
		LOGGING_STREAM.write("<")
		print(
			"\t".join([
				row['rev_id'],
				getSingleDiff(args.uri, row['rev_id']).replace("\\", "\\\\").replace("\n", "\\n").replace("\t", "\\t")
			])
		)
		LOGGING_STREAM.write(">")
		#rowBuffer.append(row)
		#if len(rowBuffer) == 50:
		#	LOGGING_STREAM.write("\n")
		#	diffMap = buildDiffMap(args.uri, list(r['rev_id'] for r in rowBuffer))
		#	for row in rowBuffer:
		#		LOGGING_STREAM.write(">")
		#		print(
		#			"\t".join([
		#				row['rev_id'],
		#				diffMap.get(row['rev_id'], '').replace("\\", "\\\\").replace("\n", "\\n").replace("\t", "\\t").encode('utf-8')
		#			])
		#		)
		#	
		#	rowBuffer = []
		#	
		#	LOGGING_STREAM.write("\n")
	
	LOGGING_STREAM.write("\n")	
	#diffMap = buildDiffMap(args.uri, list(r['rev_id'] for r in rowBuffer))
	#for row in rowBuffer:
	#	LOGGING_STREAM.write(">")
	#	print(
	#		"\t".join([
	#			row['rev_id'],
	#			diffMap.get(row['rev_id'], '').replace("\\", "\\\\").replace("\n", "\\n").replace("\t", "\\t").encode('utf-8')
	#		])
	#	)
	#
	#LOGGING_STREAM.write("\n")
				
			

def getSingleDiff(uri, revId):
	response = urllib2.urlopen(
		uri,
		urllib.urlencode({
			'action':   'query',
			'prop':     'revisions',
			'revids':   revId,
			'rvprop':   'ids|content',
			'rvdiffto': 'prev',
			'format':   'json'
		}),
	).read()
	result = json.loads(response)
	diffMap = {}
	try:
		for page in result['query']['pages'].values():
			for rev in page['revisions']:
				return rev['diff'].get("*", "").encode('utf-8')
	except Exception as e:
		logging.error(response)
		logging.error(result)
		raise e
	

def buildDiffMap(uri, revIds):
	if len(revIds) == 0:
		return {}
	else:
		response = urllib2.urlopen(
			uri,
			urllib.urlencode({
				'action':   'query',
				'prop':     'revisions',
				'revids':   '|'.join(revIds),
				'rvprop':   'ids|content',
				'rvdiffto': 'prev',
				'format':   'json'
			}),
		).read()
		result = json.loads(response)
		diffMap = {}
		try:
			for page in result['query']['pages'].values():
				for rev in page['revisions']:
					diffMap[str(rev['revid'])] = rev['diff'].get("*", "")
		except Exception as e:
			logging.error(response)
			logging.error(result)
			raise e
			
		return diffMap
		
			

def readTSVFile(f):
	headers = f.readline().strip().split("\t")
	for line in f:
		values = line.strip().split("\t")
		yield dict(zip(headers,values))
	


if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description=
			'Adds diff information to a sample of talk edits'
	)
	parser.add_argument(
		'-u', '--uri',
		type=str, 
		help='the uri of the api to connect to (defaults to enwp api)',
		default="http://en.wikipedia.org/api.php"
	)
	parser.add_argument(
		'-i', '--input',
		metavar="<path>",
		type=lambda fn:open(fn, "r"), 
		help='the sample file to read (defaults to standard in)',
		default=sys.stdin
	)
	args = parser.parse_args()
	main(args)
