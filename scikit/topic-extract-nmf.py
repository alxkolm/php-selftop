__author__ = 'alxkolm'

import argparse
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.decomposition import NMF

n_topics = 20
n_top_words = 7

parser = argparse.ArgumentParser()
parser.add_argument("filename", help="The filename to be processed")
args = parser.parse_args()

# read lines
if args.filename:
    with open(args.filename) as f:
        titles = [line.strip('\n') for line in f]

# extract feature
vectorizer = TfidfVectorizer(token_pattern=ur'\b\w[\w/.:-]+\b')
# vectorizer = TfidfVectorizer()
x_train = vectorizer.fit_transform(titles)

nmf = NMF(n_components=n_topics, random_state=1).fit(x_train)

feature_names = vectorizer.get_feature_names()

for topic_idx, topic in enumerate(nmf.components_):
    print("Topic #%d:" % topic_idx)
    print(" ".join([feature_names[i]
                    for i in topic.argsort()[:-n_top_words - 1:-1]]))
