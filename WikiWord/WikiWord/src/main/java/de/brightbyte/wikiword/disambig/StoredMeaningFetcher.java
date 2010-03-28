package de.brightbyte.wikiword.disambig;

import java.util.List;

import de.brightbyte.data.cursor.DataSet;
import de.brightbyte.util.PersistenceException;
import de.brightbyte.wikiword.model.LocalConcept;
import de.brightbyte.wikiword.store.LocalConceptStore;
import de.brightbyte.wikiword.store.WikiWordConceptStore.ConceptQuerySpec;

public class StoredMeaningFetcher implements MeaningFetcher<LocalConcept> {
	protected LocalConceptStore  store; 
	protected ConceptQuerySpec spec;
	
	public StoredMeaningFetcher(LocalConceptStore  store) {
		this(store, null);
	}
	
	public StoredMeaningFetcher(LocalConceptStore  store, ConceptQuerySpec type) {
		if (store==null) throw new NullPointerException();
		
		this.store = store;
		this.spec = type;
	}

	public List<LocalConcept> getMeanings(String term) throws PersistenceException {
		DataSet<LocalConcept> m = store.getMeanings(term, spec); //FIXME: filter/cut-off rules, sort order! //XXX: relevance value?
		return m.load();
	}

}
