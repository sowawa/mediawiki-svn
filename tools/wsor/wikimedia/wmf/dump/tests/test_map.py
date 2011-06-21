import sys, logging
from nose.tools import eq_
from gl import wp
from . import sample
from ..map import map


def test_simple_map():
	dumps = [sample.getSmallXMLFilePath(), sample.getLargeXMLFilePath()]
	
	def processPage(dump, page):
		assert hasattr(dump, "namespaces")
		assert hasattr(page, "readRevisions")
		
		count = 0
		for rev in page.readRevisions():
			count += 1
			if count >= 100: break
		
		yield (page.getId(), count)
	
	output = dict(map(dumps, processPage))
	
	eq_(output[17500012], 1)
	eq_(output[12], 100)
