#! /usr/bin/python
# -*- coding: utf-8 -*-

__author__="esemi"
import numpy
import matplotlib;
from scipy.cluster.vq import *
import pylab
pylab.close()

import sys;

xy = numpy.array([
                [1,1,3],
                [2,2,4],
                [3,3,5],
                [1,2,6],
                [3,2,7],
                ]);

print xy;

pylab.scatter3D(xy[:,0],xy[:,1],xy[:,2])
pylab.show();

sys.exit();

# make some z vlues
z = numpy.sin(xy[:,1]-0.2*xy[:,1])

# whiten them
z = whiten(z)

# let scipy do its magic (k==3 groups)
res, idx = kmeans2(numpy.array(zip(xy[:,0],xy[:,1],z)),3)

# convert groups to rbg 3-tuples.
colors = ([([0,0,0],[1,0,0],[0,0,1])[i] for i in idx])

# show sizes and colors. each color belongs in diff cluster.
pylab.scatter(xy[:,0],xy[:,1],s=20*z+9, c=colors)
pylab.savefig('/var/www/tmp/clust.png')