__author__ = 'alxkolm@bk.ru'

import argparse
from time import time
from sklearn.feature_extraction.text import TfidfVectorizer, CountVectorizer
from sklearn.decomposition import LatentDirichletAllocation

def print_top_words(model, feature_names, n_top_words):
    for topic_idx, topic in enumerate(model.components_):
        print("Topic #%d:" % topic_idx)
        print(" ".join([feature_names[i] for i in topic.argsort()[:-n_top_words - 1:-1]]))
    print()


n_features = 1000
n_topics = 10
n_top_words = 7

parser = argparse.ArgumentParser()
parser.add_argument("filename", help="The filename to be processed")
args = parser.parse_args()

# read lines
if args.filename:
    with open(args.filename) as f:
        titles = [line.strip('\n') for line in f]


# Use tf (raw term count) features for LDA.
print("Extracting tf features for LDA...")
tf_vectorizer = CountVectorizer(max_df=0.95, min_df=2, max_features=n_features,
                                stop_words='english')
t0 = time()
tf = tf_vectorizer.fit_transform(titles)
print("done in %0.3fs." % (time() - t0))

print("Fitting LDA models with tf features, n_samples=%d and n_features=%d..."
      % (len(titles), n_features))
lda = LatentDirichletAllocation(n_topics=n_topics, max_iter=500,
                                learning_method='online', learning_offset=50.,
                                random_state=1)
t0 = time()
lda.fit(tf)
print("done in %0.3fs." % (time() - t0))

print("\nTopics in LDA model:")
tf_feature_names = tf_vectorizer.get_feature_names()
print_top_words(lda, tf_feature_names, n_top_words)

