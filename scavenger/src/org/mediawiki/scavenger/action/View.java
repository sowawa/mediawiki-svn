package org.mediawiki.scavenger.action;

import org.mediawiki.scavenger.Page;
import org.mediawiki.scavenger.PageFormatter;
import org.mediawiki.scavenger.Revision;

public class View extends PageAction {
	Page page;
	Revision viewing;
	String formattedText;
	
	protected String pageExecute() throws Exception {
		if (title == null)
			return "mainpage";
	
		page = wiki.getPage(title);

		formattedText = null;
		if (page.exists()) {
			/*
			 * If the user requested a page with a non-canonical name
			 * (wrong case), redirect them.
			 */
			if (!page.getTitle().getText().equals(title.getText())) {
				req.setAttribute("pagename", page.getTitle().getText());
				return "viewpage";
			}

			String rev = req.getParameter("rev");
			if (rev != null)
				viewing = wiki.getRevision(Integer.parseInt(rev));
			else
				viewing = page.getLatestRevision();

			PageFormatter formatter = new PageFormatter(wiki);
			formattedText = formatter.getFormattedText(viewing);
		}
		
		req.setAttribute("viewing", viewing);
		req.setAttribute("formattedText", formattedText);
		return "view";
	}
}
