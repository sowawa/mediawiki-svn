
from setuptools import setup, find_packages

setup(
	name='util',
	version='1.0',
	description="WMF utilities",
	long_description="""
		A set of utilities originally authored by Aaron Halfaker 
		during the 2011 Wikimedia Summer of Research.  The utilities 
		in this package are intended to aid in processing of 
		MediaWiki data related to Wikimedia projects.  Many of the 
		utilities have been specifically designed to allow 
		processing of the massive about of data (currently) found 
		in the full history dump of the English Wikipedia
	""",
	author='Aaron Halfaker',
	author_email='aaron.halfaker@gmail.com',
	url='http://meta.wikimedia.org/wiki/User:EpochFail',
	packages=find_packages(),
	entry_points = {
		'console_scripts': ['dump_map = wmf.dump.map:main']
	}
)
