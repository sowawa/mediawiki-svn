import sys, subprocess, os, errno, re, argparse, logging, hashlib, types
from difflib import SequenceMatcher
from gl.containers import LimitedDictLists

from text import STOP_WORDS, MARKUP

	
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



def process(page, output):
	recentRevs = LimitedDictLists(maxsize=15)
	lastTokens = []
	for revision in page.readRevisions():
		checksum = hashlib.md5(revision.getText().encode("utf-8")).hexdigest()
		if checksum in recentRevs:
			#found a revert
			revertedToRev = recentRevs[checksum]
			
			#get the revisions that were reverted
			revertedRevs = [r for (c, r) in recentRevs if r.getId() > revertedToRev.getId()]
			
			#write revert row
			revert.write(
				['revert']+
				[
					revision.getId(), 
					revertedToRev.getId(), 
					len(revertedRevs)
				]
			)
			
			for rev in revertedRevs:
				out.push(
					['reverted']+
					[
						rev.getId(),
						revision.getId(),
						revertedToRev.getId(), 
						len(revertedRevs)
					]
				)
		else:
			pass
		
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
			
		
		output.pushRow(['meta']+[row[h] for h in metaHeaders])
		
		lastTokens = tokens
