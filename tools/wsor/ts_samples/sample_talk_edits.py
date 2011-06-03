#
# Sample talk page postings to newbie's talk pages in various languages.
#
# This script is intended to be run on the one of the toolserver machines.
#
# run python sample_talk_edits.py --help for command line parameters.
#
import os, sys, logging, argparse, MySQLdb

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
	
	logging.info("Connecting to %s_p using %s." % (args.db, args.cnf))
	conn = MySQLdb.connect(
		host="%s-p.rrdb.toolserver.org" % args.db, 
		db='%s_p' % args.db, 
		read_default_file=args.cnf
	)
	fetchConn = MySQLdb.connect(
		host="%s-p.rrdb.toolserver.org" % args.db, 
		db='%s_p' % args.db, 
		read_default_file=args.cnf
	)
	
	#Printing headers
	print(
		"\t".join([
			'user_id',
			'username',
			'registration',
			'end_of_newbie',
			'rev_id',
			'timestamp',
			'comment'
		])
	)
	for year in args.year:
		logging.info("Processing %s:" % year)
		yearCount = 0
		for user in getUsersByYear(fetchConn, year):
			initialRevs = list(getFirst10Revs(conn, user['user_id']))
			if len(initialRevs) > 0:
				endOfNoob = initialRevs[-1]['rev_timestamp']
				talkRev = getRandNonSelfPostToTalkPage(
					conn,
					user['user_id'],
					user['user_name'],
					user['user_registration'],
					endOfNoob
				)
				if talkRev != None:
					print(
						"\t".join(clean(v) for v in [
							user['user_id'],
							user['user_name'],
							user['user_registration'],
							endOfNoob,
							talkRev['rev_id'],
							talkRev['rev_timestamp'],
							talkRev['rev_comment']
						])
					)
					LOGGING_STREAM.write(".")
					yearCount += 1
					if yearCount >= args.n:
						break
				else:
					LOGGING_STREAM.write("s")
					#logging.debug("User %s has no talk page revisions by other users. Skipping..." % user['username'])
				
			else:
				LOGGING_STREAM.write("-")
				#logging.debug("User %s has no revisions. Skipping..." % user['username'])
			
		LOGGING_STREAM.write("\n")
			
		
		
	
def getUsersByYear(conn, year):
	year  = int(year)
	cursor = conn.cursor(MySQLdb.cursors.SSCursor)
	yearBegin = "%s0000000000" % year
	yearEnd   = "%s1231115959" % year
	cursor.execute("""
		SELECT * FROM user
		WHERE user_registration BETWEEN %(year_begin)s AND %(year_end)s
		ORDER BY RAND()
		""",
		{
			'year_begin': yearBegin,
			'year_end': yearEnd
		}
	)
	for row in cursor:
		yield dict(
			zip(
				(d[0] for d in cursor.description),
				row
			)
		)
			
	


def getFirst10Revs(conn, userId):
	user_id = int(userId)
	cursor = conn.cursor()
	cursor.execute("""
		SELECT * FROM revision
		WHERE rev_user = %(user_id)s
		ORDER BY rev_timestamp ASC
		LIMIT 10
		""",
		{
			'user_id': userId
		}
	)
	for row in cursor:
		yield dict(
			zip(
				(d[0] for d in cursor.description),
				row
			)
		)

def getRandNonSelfPostToTalkPage(conn, userId, username, start, end):
	pageId = getTalkPageId(conn, username)
	if pageId == None: return None
	else:
		cursor = conn.cursor()
		cursor.execute("""
			SELECT * FROM revision
			WHERE rev_page = %(page_id)s
			AND rev_user != %(user_id)s
			AND rev_timestamp BETWEEN %(start)s AND %(end)s
			ORDER BY RAND()
			LIMIT 1
			""",
			{
				'page_id': pageId,
				'user_id': userId,
				'start':   start,
				'end':     end
			}
		)
		for rev in cursor:
			return dict(
				zip(
					(d[0] for d in cursor.description),
					rev
				)
			)
		
		return None
	

def getTalkPageId(conn, title):
	cursor = conn.cursor()
	cursor.execute("""
		SELECT page_id FROM page 
		WHERE page_title = %(title)s
		AND page_namespace = 3
		""",
		{
			'title': title
		}
	)
	for page in cursor:
		return page[0]
	
	return None
	
if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description=
			'Samples editors by the year they made their first edit.'
	)
	parser.add_argument(
		'n',
		type=int, 
		help='the number of editors to sample from each year'
	)
	parser.add_argument(
		'year',
		type=int, 
		help='year(s) to sample from',
		nargs="+"
	)
	parser.add_argument(
		'-c', '--cnf',
		metavar="<path>",
		type=str, 
		help='the path to MySQL config info (defaults to ~/.my.cnf)',
		default=os.path.expanduser("~/.my.cnf")
	)
	parser.add_argument(
		'-d', '--db',
		type=str, 
		help='the language db to run the query in (defaults to enwiki)',
		default="enwiki"
	)
	args = parser.parse_args()
	main(args)
