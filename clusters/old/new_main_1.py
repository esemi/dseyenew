#! /usr/bin/python
# -*- coding: utf-8 -*-


from hcluster import pdist, linkage, dendrogram;
import matplotlib.pyplot as plt;
from numpy.random import rand;

X = rand(12,8)
X[0:5,:] *= 2
print X;
Y = pdist(X);
Z = linkage(Y)

mon = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
R = dendrogram(Z, labels = mon, leaf_font_size = 15, orientation='left', color_threshold=1.2)
print R, Z;
plt.savefig('test');
#plt.show();
