from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import spacy

nlp = spacy.load("en_core_web_sm")

app = FastAPI()

class TaskPost(BaseModel):
    text: str

UNWANTED_WORDS = {"goal", "task", "learn", "study", "build", "improve", "want"}

@app.post("/extract-interests")
def extract_interests(task_post: TaskPost):
    try:
        doc = nlp(task_post.text)
        
        # Extract nouns and proper nouns
        keywords = [
            token.text.lower()
            for token in doc
            if token.pos_ in ["NOUN", "PROPN"] and token.text.lower() not in UNWANTED_WORDS
        ]        
        
        return {"interests": keywords}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))