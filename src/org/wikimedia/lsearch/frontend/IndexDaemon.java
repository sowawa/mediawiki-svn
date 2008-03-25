/*
 * Created on Feb 2, 2007
 *
 */
package org.wikimedia.lsearch.frontend;

import org.wikimedia.lsearch.beans.Title;
import org.wikimedia.lsearch.index.IndexThread;
import org.wikimedia.lsearch.index.IndexUpdateRecord;

/**
 * Abstracts operations of indexer for various frontends. 
 * 
 * @author rainman
 *
 */
public class IndexDaemon {
	static protected IndexThread indexer = null; 

	public IndexDaemon() {
		if(indexer == null){
			indexer = new IndexThread();
			indexer.start();
		}
	}
	@Deprecated
	public void updatePage(String databaseName, String pageId, Title title, String isRedirect, String text ) {
		// FIXME: ranks & redirects are ignored!
		IndexThread.enqueue(new IndexUpdateRecord(databaseName, Long.parseLong(pageId), title, text, null, 1, IndexUpdateRecord.Action.UPDATE));
	}
	@Deprecated
	public void deletePage(String databaseName, String pageId, Title title) {
		IndexThread.enqueue(new IndexUpdateRecord(databaseName,Long.parseLong(pageId),title,"",null, 1, IndexUpdateRecord.Action.DELETE));
	}
	@Deprecated
	public void addPage(String databaseName, String pageId, Title title, String text) {
		IndexThread.enqueue(new IndexUpdateRecord(databaseName, Long.parseLong(pageId), title, text, null, 1, IndexUpdateRecord.Action.ADD));
	}

	public String getStatus() {
		return indexer.getStatus();
	}

	public void stop() {
		indexer.stopThread();
	}

	public void start() {
		indexer.startThread();
	}

	public void flushAll() {
		indexer.flush();
	}

	public void quit() {
		indexer.quit();
	}

	public void makeSnapshots(){
		makeSnapshots("");
	}
	
	public void makeSnapshots(String pattern){
		indexer.makeSnapshotsNow(pattern,false);
	}
	
	public void snapshotPrecursors(){
		snapshotPrecursors("");
	}
	public void snapshotPrecursors(String pattern){
		indexer.makeSnapshotsNow(pattern,true);
	}

}
