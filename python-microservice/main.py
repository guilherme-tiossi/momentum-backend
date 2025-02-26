from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import spacy
import pytextrank
from nltk.corpus import wordnet as wn
import nltk
import numpy as np

# Download WordNet data
nltk.download('wordnet')

# Load spaCy model with word vectors
nlp = spacy.load("en_core_web_lg")  # Use a model with static word vectors
nlp.add_pipe("textrank")

app = FastAPI()

class TaskPost(BaseModel):
    text: str

@app.post("/extract-interests")
def extract_interests(task_post: TaskPost):
    try:
        # Process the text with spaCy
        doc = nlp(task_post.text)

        # Extract key phrases using PyTextRank
        key_phrases = [phrase.text for phrase in doc._.phrases if phrase.rank > 0.1]  # Adjust threshold

        # Deduplicate phrases
        interests = list(set(key_phrases))

        # Expand keywords with related terms
        expanded_interests = []
        for keyword in interests:
            expanded_interests.append(keyword)
            expanded_interests.extend(get_related_terms(keyword))
            expanded_interests.extend(get_semantic_related_terms(keyword))

        # Convert all terms to lowercase and deduplicate
        expanded_interests = list(set([term.lower() for term in expanded_interests]))
        
        return {"interests": expanded_interests}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

def get_related_terms(keyword):
    """Get related terms using WordNet."""
    related_terms = set()
    for synset in wn.synsets(keyword):
        for lemma in synset.lemmas():
            related_terms.add(lemma.name().replace("_", " "))
            for related in lemma.derivationally_related_forms():
                related_terms.add(related.name().replace("_", " "))
    return list(related_terms)

def get_semantic_related_terms(keyword, n=5):
    """Get semantically related terms using spaCy's word vectors."""
    if not nlp.vocab.has_vector(keyword):
        return []  # Skip if the keyword has no vector
    
    # Get the keyword's vector
    keyword_vector = nlp.vocab.get_vector(keyword)
    
    # Find the most similar vectors in the vocabulary
    queries = np.array([keyword_vector])
    most_similar = nlp.vocab.vectors.most_similar(queries, n=n)
    
    # Extract the similar words
    similar_words = []
    for key in most_similar[0][0]:
        word = nlp.vocab.strings[key]
        if word != keyword and word.isalpha():  # Filter out non-alphabetic terms
            similar_words.append(word)
    
    return similar_words