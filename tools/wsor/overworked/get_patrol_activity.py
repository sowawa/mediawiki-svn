import MySQLdb, MySQLdb.cursors, re



AUTO_PATROLLED = re.compile(r'[0-9]+\n[0-9]\n1')

class DB:
	
	def __init__(self, conn):
		self.conn = conn
	
	def getPatrolerDays(self, removeAuto=True, namespaces=[], bots=):
		cursor = self.conn.cursor(MySQLdb.cursors.SSDictCursor)
		query = """
			SELECT 
				l.log_user, 
				u.user_name, 
				SUBSTRING(log_timestamp,1,4), 
				SUBSTRING(log_timestamp,5,2), 
				SUBSTRING(log_timestamp,5,2)
			FROM logging l
			INNER JOIN user u
			ON u.user_id = l.log_user
			WHERE log_action = "patrol"
			AND log_type = "patrol"
			GROUP BY 
				l.log_user, 
				u.user_name, 
				SUBSTRING(log_timestamp,1,4), 
				SUBSTRING(log_timestamp,5,2), 
				SUBSTRING(log_timestamp,5,2)
		"""
		if removeAuto:
			query += "AND log_params NOT REGEXP '[0-9]+\\n[0-9]\\n1'\n"
		
		if len(namespaces) > 0:
			query += "AND log_namespace in (" + ",".join(str(int(n)) for n in namespaces) + ")"
		
		cursor.execute(query)
		
		return cursor
			
		

def main(args):
	
	
if __name__ == "__main__":
	parser = argparse.ArgumentParser(
		description='Produces a list of patrol events.'
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
				
				
