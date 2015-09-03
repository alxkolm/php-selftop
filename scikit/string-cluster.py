__author__ = 'alxkolm'
import numpy as np
import sys
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.cluster import KMeans
from sklearn.cluster import MeanShift, estimate_bandwidth

# read lines

titles = [line.strip('\n') for line in sys.stdin]

# extract feature
vectorizer = TfidfVectorizer(token_pattern=ur'\b\w[\w/.:-]+\b')
x_train = vectorizer.fit_transform(titles)

# clustering
estimator = KMeans(n_clusters=4)
labels = estimator.fit_predict(x_train)


for x in zip(titles, labels):
    print x[1], x[0]
