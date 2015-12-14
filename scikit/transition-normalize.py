import argparse
import numpy
from scipy.sparse import coo_matrix
from sklearn.cluster import AffinityPropagation
from sklearn import preprocessing

parser = argparse.ArgumentParser()
parser.add_argument("filename", help="The filename to be processed")
args = parser.parse_args()

index_matrix = numpy.loadtxt(args.filename, dtype="u4")
row = index_matrix[:,0]
col = index_matrix[:,1]
data = index_matrix[:,1].astype('f8')


matrix = coo_matrix((data, (row, col)))
matrix_normalized = preprocessing.normalize(matrix, norm="l1", axis=0)
cx = coo_matrix(matrix_normalized)
for i, j, v in zip(cx.row, cx.col, cx.data):
    print "%d\t%d\t%f" % (i, j, v)




