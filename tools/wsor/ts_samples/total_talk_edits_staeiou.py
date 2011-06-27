#
# Sample talk page postings to newbie's talk pages in various languages.
#
# This script is intended to be run on the one of the toolserver machines.
#
# run python sample_talk_edits.py --help for command line parameters.
#
import os, sys, logging, argparse, MySQLdb, datetime

def clean(v):
	if v == None:
		return "\N"
	else:
		return str(v).replace("\\", "\\\\").replace("\t", "\\t").replace("\n", "\\n")
	

def main(args):
	LOGGING_STREAM = sys.stderr
	logging.basicConfig(
		level=logging.DEBUG,
		stream=LOGGING_STREAM,
		format='%(asctime)s %(levelname)-8s %(message)s',
		datefmt='%b-%d %H:%M:%S'
	)
	
	logging.info("Connecting to %s:%s using %s." % (args.host, args.db, args.cnf))
	conn = MySQLdb.connect(
		host=args.host, 
		db=args.db, 
		read_default_file=args.cnf
	)
	fetchConn = MySQLdb.connect(
		host=args.host, 
		db=args.db, 
		read_default_file=args.cnf
	)
	
	#Printing headers
	print(
		"\t".join([
			'user_id',
			'username',
			'registration',
			'first_edit',
			'end of newbie',
			'last user rev_id',
			'last utalk rev_id',
			'Main edits',
			'Talk edits',
			'User edits',
			'User_talk edits',
			'Wikipedia edits',
			'Wikipedia_talk edits',
			'Image edits',
			'Image_talk edits',
			'MediaWiki edits',
			'MediaWiki_talk edits',
			'Template edits',
			'Template_talk edits',
			'Help edits',
			'Help_talk edits',
			'Category edits',
			'Category_talk edits',
			'blocks'
		])
	)
	for year in args.year:
		for semStart, semEnd in [('000000', '069999'), ('070000', '99999')]:
			logging.info("Processing %s:%s" % (year, semStart))
			start = str(year) + semStart + "000000"
			end   = str(year) + semEnd + "999999"
			count = 0
			for user in getUsers(fetchConn, start, end):
				#
				# The following lines take a user's first_edit, 
				# covert it to a date, add 30 days and convert 
				# it back to a string.  I am syntax fu.
				#
				endOfNoob = (
					datetime.date(
						int(user['first_edit'][0:4]),
						int(user['first_edit'][4:6]),
						int(user['first_edit'][6:8])
					)+datetime.timedelta(days=30)
				).strftime("%Y%m%d") + user['first_edit'][8:]
				
				LOGGING_STREAM.write(":")
				talkRevs = list(getPostsToTalkPage(
					conn,
					user['user_id'],
					user['user_name'],
					user['first_edit'],
					endOfNoob
				))
				newbieRevs = {}
				
				LOGGING_STREAM.write(":")
				for rev in getUserRevs(conn, user['user_id'], user['first_edit'], endOfNoob):
					newbieRevs[rev['page_namespace']] = newbieRevs.get(rev['page_namespace'], 0)+1
				
				
				LOGGING_STREAM.write(":")
				blocks = '\n'.join(
					[
						"%(action)s: %(comment)s - %(params)s" % b for b in
						getBlockEvents(conn, user['user_name'], user['first_edit'], endOfNoob)
					]
				)
				
				LOGGING_STREAM.write(":")
				userPageRev = getLastPostToUserPage(
					conn, 
					user['user_id'], 
					user['user_name'],
					user['first_edit'],
					endOfNoob
				)
				if userPageRev == None:
					userPageRevId = None
				else:
					userPageRevId = userPageRev['rev_id']
				
				if len(talkRevs) != 0:
					print(
						"\t".join(clean(v) for v in [
							user['user_id'],
							user['user_name'],
							user['user_registration'],
							user['first_edit'],
							endOfNoob,
							userPageRevId,
							talkRevs[-1]['rev_id'],
							newbieRevs.get(0,  0),
							newbieRevs.get(1,  0),
							newbieRevs.get(2,  0),
							newbieRevs.get(3,  0),
							newbieRevs.get(4,  0),
							newbieRevs.get(5,  0),
							newbieRevs.get(6,  0),
							newbieRevs.get(7,  0),
							newbieRevs.get(8,  0),
							newbieRevs.get(9,  0),
							newbieRevs.get(10, 0),
							newbieRevs.get(11, 0),
							newbieRevs.get(12, 0),
							newbieRevs.get(13, 0),
							newbieRevs.get(14, 0),
							newbieRevs.get(15, 0),
							blocks
						])
					)
					LOGGING_STREAM.write(".")
					count += 1
					if count >= args.n:
						break
				else:
					LOGGING_STREAM.write("s")
				
			LOGGING_STREAM.write("\n")
			
		
		
	
def getUsers(conn, start, end):
	cursor = conn.cursor(MySQLdb.cursors.SSCursor)
	cursor.execute("""
		SELECT 
			u.user_id,
			u.user_name,
			u.user_registration,
			um.first_edit,
			um.last_edit
		FROM user u
		INNER JOIN halfak.user_meta um
			ON u.user_id = um.user_id
		WHERE um.first_edit BETWEEN %(start)s AND %(end)s
		ORDER BY RAND()
		""",
		{
			'start': start,
			'end':   end
		}
	)
	for row in cursor:
		yield dict(
			zip(
				(d[0] for d in cursor.description),
				row
			)
		)
			
	


def getUserRevs(conn, userId, start, end):
	user_id = int(userId)
	cursor = conn.cursor()
	cursor.execute("""
		SELECT 
			r.*,
			p.page_namespace
		FROM revision r
		INNER JOIN page p
			ON r.rev_page = p.page_id
			WHERE rev_user = %(user_id)s
		AND rev_timestamp BETWEEN %(start)s AND %(end)s
		ORDER BY rev_timestamp ASC
		""",
		{
			'user_id':  userId,
			'start':    start,
			'end':      end
		}
	)
	for row in cursor:
		yield dict(
			zip(
				(d[0] for d in cursor.description),
				row
			)
		)
	

def getBlockEvents(conn, username, start, end):
	cursor = conn.cursor()
	cursor.execute("""
		SELECT 
			log_action as action,
			log_comment as comment, 
			log_params as params
		FROM logging
		WHERE log_title = %(username)s
		AND log_type = "block"
		AND log_timestamp BETWEEN %(start)s AND %(end)s
		ORDER BY log_timestamp ASC
		""",
		{
			'username': username,
			'start':    start,
			'end':      end
		}
	)
	for row in cursor:
		yield dict(
			zip(
				(d[0] for d in cursor.description),
				row
			)
		)

def getLastPostToUserPage(conn, userId, username, start, end):
	pageId = getPageId(conn, username, 2)
	if pageId != None: 
		cursor = conn.cursor()
		cursor.execute("""
			SELECT * FROM revision
			WHERE rev_page = %(page_id)s
			AND rev_timestamp BETWEEN %(start)s AND %(end)s
			ORDER BY rev_timestamp DESC
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
	

def getPageId(conn, title, namespace):
	cursor = conn.cursor()
	cursor.execute("""
		SELECT page_id FROM page 
		WHERE page_title = %(title)s
		AND page_namespace = %(namespace)s
		""",
		{
			'title': title,
			'namespace': namespace
		}
	)
	for page in cursor:
		return page[0]
	
	return None

def getPostsToTalkPage(conn, userId, username, start, end):
	pageId = getPageId(conn, username, 3)
	if pageId != None: 
		cursor = conn.cursor()
		cursor.execute("""
			SELECT * FROM revision
			WHERE rev_page = %(page_id)s
			AND rev_timestamp BETWEEN %(start)s AND %(end)s
			ORDER BY rev_id
			""",
			{
				'page_id': pageId,
				'user_id': userId,
				'start':   start,
				'end':     end
			}
		)
		for rev in cursor:
			yield dict(
				zip(
					(d[0] for d in cursor.description),
					rev
				)
			)
	
	
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
		'-s', '--host',
		type=str, 
		help='the database host to connect to (defaults to localhost)',
		default="localhost"
	)
	parser.add_argument(
		'-d', '--db',
		type=str, 
		help='the language db to run the query in (defaults to enwiki)',
		default="enwiki"
	)
	args = parser.parse_args()
	main(args)
