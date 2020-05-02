#! /usr/bin/env python
# -*- coding: utf-8 -*-
#INFO:root:Numpy: 1.5.1
#INFO:root:Scipy: 0.8.0
#INFO:root:Matplotlib: 0.99.3

__author__="esemi"

import main as worker;
import data as model;
import sys;
from scipy.spatial.distance import pdist;
from scipy.cluster import hierarchy;
from optparse import OptionParser;

reload(sys);
sys.setdefaultencoding( "utf8" );
sys.setrecursionlimit(10000);
    

if __name__ == "__main__":
    import logging;
    import time;

    start1 = time.time();
    
    logging.basicConfig( level = logging.DEBUG );
        
    logging.info("Start");    

    parser = OptionParser();
    parser.add_option('-f', '--filename',
                      dest='filename',
                      default='терранова',
                      help='name csv file for world ranks [терранова]')
    (options, args) = parser.parse_args();

    #подготовка данных
    niks,cols,data,rec = model.get_data("%s%s.csv" % (worker.CSV_PATH, options.filename) );
    logging.info("Prepared %d players and %d colums" % (len(niks), len(cols)) );
    
    
    logging.info("\nFirstStep");

    logging.info("Distance euclidean");
    start = time.time();
    euclid_data = pdist(data, 'euclidean');
    logging.info("Time: %s" % (time.time() - start));

    logging.info("Clustering start");
    start = time.time();
    Z = hierarchy.complete(euclid_data);
    worker.hierarchy_draw(Z, niks, 'study_complete_euclid', 0.4);
    logging.info("Time complete: %s" % (time.time() - start));

    start = time.time();
    Z = hierarchy.average(euclid_data);
    worker.hierarchy_draw(Z, niks, 'study_average_euclid', 0.25);
    logging.info("Time average: %s" % (time.time() - start));

    start = time.time();
    Z = hierarchy.weighted(euclid_data);
    worker.hierarchy_draw(Z, niks, 'study_weighted_euclid', 0.25);
    logging.info("Time weighted: %s" % (time.time() - start));



    logging.info("\nSecondStep");

    logging.info("Distance other");
    start = time.time();
    sqeuclid_data = pdist(data, 'sqeuclidean');
    cityblock_data = pdist(data, 'cityblock');
    logging.info("Time: %s" % (time.time() - start));

    logging.info("Clustering start");

    start = time.time();
    Z = hierarchy.average(sqeuclid_data);
    worker.hierarchy_draw(Z, niks, 'study_average_sqeuclidean', 0.25);
    logging.info("Time average sqeuclidean: %s" % (time.time() - start));

    start = time.time();
    Z = hierarchy.average(cityblock_data);
    worker.hierarchy_draw(Z, niks, 'study_average_cityblock', 0.25);
    logging.info("Time average cityblock: %s" % (time.time() - start));


    logging.info("End %s" % (time.time() - start1));
    

