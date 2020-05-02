#! /usr/bin/env python
# -*- coding: utf-8 -*-
#INFO:root:Numpy: 1.5.1
#INFO:root:Scipy: 0.8.0
#INFO:root:Matplotlib: 0.99.3
#INFO:root:NetworkX: 1.6

__author__ = "esemi";

import os
import sys

import data as model
import matplotlib.pyplot as plt
import networkx as nx
import numpy
from scipy.cluster import *
from scipy.spatial.distance import cdist, pdist


reload(sys);
sys.setdefaultencoding( "utf8" );
sys.setrecursionlimit(10000);

if( os.uname()[1] == 'vm9057.majordomo.ru' ):
    PUBLIC_PATH = os.path.abspath(os.sep.join([sys.path[0], '..', '..', 'www', 'clusters'])) + os.sep; #path to public folder for results
    CSV_PATH = os.path.abspath(os.sep.join([sys.path[0], '..', '..', 'www', 'csv'])) + os.sep; #path to public folder of csv files
    LOG_PATH = os.path.abspath(os.sep.join([sys.path[0], '..', '..', 'logs'])) + os.sep;
else:
    PUBLIC_PATH = sys.path[0] + os.sep + 'results' + os.sep;
    CSV_PATH = sys.path[0] + os.sep + 'data' + os.sep;
    LOG_PATH = sys.path[0] + os.sep;

def tanimoto(u, v):
    """Рассчёт коэфициента танимото"""
    u, v = set(u), set(v);
    return 1. * len(u & v) / len(u | v);
    

def kmeans_export(centroids, data, labels):
    """Выгрузка результатов kmeans"""

    res = [ [] for i in range(len(centroids)) ];
    D = cdist(numpy.array(data), centroids, 'euclidean');
    for i, l in enumerate(D):
        res[l.tolist().index(l.min())].append(labels[i]);
    
    return res;

def graph_export(G):
    """Извлечение отдельных кластеров из графа"""
        
    return [ item[1]['members'] for item in G.nodes(data=True)];

def hierarchy_export(Z, labels, limit):
    """Извлечение отдельных кластеров из дендрограммы"""

    clusters = dict([(i, [item]) for i, item in enumerate(labels)]);
    num = len(labels);    
    for edge in Z:
        if(edge[2] > limit): break;            
        x1 = clusters.pop( int(edge[0]) );
        x2 = clusters.pop( int(edge[1]) );
        clusters[num] = x1 + x2;
        num += 1;
    res = [clusters[i] for i in clusters]; 
    
    return res;


def compare_clusters(clust1, clust2, labels):
    """Процент схожести результатов"""

    if( len(clust1) > len(clust2) ):
        clust1, clust2 = clust2, clust1;
        
    clust = [(i, item) for i, item in enumerate(clust2)];
    dists = [];
    for i in range(len(clust1)):
        max_index = None;
        max_val = 0;
        tt = [];
        for j, item in enumerate(clust):
            tmp = tanimoto(clust1[i], item[1]);
            tt.append(tmp);
            if( tmp > max_val ):
                max_index = item[0];
                max_val = tmp;
                del clust[j];
        dists.append((i, max_index, max_val, len(clust1[i]), max_val * float(len(clust1[i])) ));
        
    return (sum([i[4] for i in dists]) / len(labels)) * 100;

def compare_and_save(H_res, G_res, K_res, labels, cols, data, name, recovery):
    """
        Сравнение результатов кастеризаций различными методами,
        расчёт типовых игроков,
        сохранение параметров типовых игроков
    """

    sorter = lambda item: len(item);

    H_res = sorted(H_res, key=sorter, reverse=True);
    G_res = sorted(G_res, key=sorter, reverse=True);
    K_res = sorted(K_res, key=sorter, reverse=True);

    model.save_clustermethod_result( PUBLIC_PATH + "%s_hierarhy.csv" % name, H_res );
    model.save_clustermethod_result( PUBLIC_PATH + "%s_graph.csv" % name, G_res );
    model.save_clustermethod_result( PUBLIC_PATH + "%s_kmeans.csv" % name, K_res );

    #сохранение графика размеров кластеров различных методов
    plt.figure(figsize=(10, 10));
    plt.plot(
             range(0, len(H_res)), [len(item) for item in H_res], 'r--',
             range(0, len(G_res)), [len(item) for item in G_res], 'g:',
             range(0, len(K_res)), [len(item) for item in K_res], 'b-')
    plt.legend( ('hierarhy', 'mst', 'kmeans'), loc=1);
    plt.xlabel('Clusters')
    plt.ylabel('Size')
    plt.savefig(PUBLIC_PATH + '%s_clusters_count' % name);

    #сравнение составов кластеров
    similarity = ( compare_clusters(H_res, G_res, labels), compare_clusters(H_res, K_res, labels) );

    #расчёт средних для кластеров иерархии, представляющих большую часть игроков
    percent = len(labels) / 100.;
    res = [{
        'size':len(cluster),
        'percent': len(cluster) / percent,
        'players':cluster,
        'params':model.recovery_data([ data[labels.index(nik)] for nik in cluster ], recovery),
    } for cluster in H_res];

    model.save_cluster_result( PUBLIC_PATH + "%s_result.csv" % name, res, cols, similarity );

    
    return similarity;




def hierarchy_draw(Z, labels, name, limit=10.0):
    """Draw dendrogram"""

    plt.figure(figsize=(20, 10));
    hierarchy.dendrogram(Z, labels=labels, color_threshold=limit,
                         leaf_font_size=5, count_sort=True, show_leaf_counts=True, no_labels=True);
    plt.savefig(PUBLIC_PATH + "%s_dendro" % name);


def graph_draw(G, name):
    """Draw graph"""

    import random;

    plt.figure();
    nx.draw(G,
            font_size=5,
            alpha=0.75,
            node_color=[(random.random(), random.random(), random.random()) for i in G.nodes()],
            node_size=[x[1]['size'] * 30 for x in G.nodes(data=True)],
            labels=dict([(x[0], x[1]['size']) for x in G.nodes(data=True)]));
    plt.savefig(PUBLIC_PATH + '%s_mst' % name);





def graph_mst(dist, labels, limit):
    """Обёртка над алгоритмом MST"""

    from collections import deque;
    
    S = nx.Graph(); #исходный граф
    S.add_nodes_from(labels);
    R = S.copy(); #результат кластеризации
    C = nx.Graph(); #читаемый результат

    dq = deque(dist);        
    len_x = len(labels);
    for x in range( len_x-1 ):
        for y in range(x + 1, len_x):
            S.add_edge( labels[x], labels[y], weight=dq.popleft() );

    mst = deque(nx.minimum_spanning_edges(S, data=True));
    del S;

    R.add_edges_from( [edge for edge in mst if( edge[2]['weight'] <= limit)] );

    for num, clust in enumerate(nx.connected_components(R)):
        C.add_node(num, {
                   'size':len(clust),
                   'members': clust
                   });
    del R;

    return C;
    



if __name__ == "__main__":
    import logging;
    import time;

    logging.basicConfig(
		filename= LOG_PATH + 'cluster_log',
		format='%(asctime)s %(levelname)s:%(message)s',
		level=logging.DEBUG);

    logging.info("Started %s" % time.strftime('%H:%M:%S %d.%m.%Y'));

    
    #настройки
    WORLDS = [
        #{'csv': 'малаягидра', 'name': 'hydra', 'cut_level': 0.45, 'count_clusters': 4, 'metric':'euclidean'},

        {'csv': 'терранова', 'name': 'terra', 'cut_level': 0.25, 'count_clusters': 8, 'metric':'euclidean'},
        #{'csv': 'весы','name': 'weight','cut_level': 0.35, 'count_clusters': 12, 'metric':'euclidean'},
        #{'csv': 'волк', 'name': 'volk','cut_level': 0.2, 'count_clusters': 17,'metric':'euclidean'},
        #{'csv': 'орион','name': 'orion','cut_level': 0.25, 'count_clusters': 9, 'metric':'sqeuclidean'},
        {'csv': 'лев','name': 'lev','cut_level': 0.25, 'count_clusters': 9,'metric':'sqeuclidean'},
        #{'csv': 'водолей','name': 'voda','cut_level': 0.20,'count_clusters': 9, 'metric':'sqeuclidean'},
    ];

    logging.info("Found %d worlds for clustering" % len(WORLDS));


    for CONFIG in WORLDS:

        logging.info(CONFIG['name'].upper());

        begin = time.time();

        #подготовка данных	
        niks, cols, data, recovery = model.get_data("%s%s_main.csv.gz" % (CSV_PATH, CONFIG['csv']));
        logging.info("Prepared %d players and %d colums" % (len(niks), len(cols)) );


        #вычисление расстояний
        logging.info("Distance compute start");
        dist = pdist(data, CONFIG['metric']);


        #Иерархическая кластеризация
        logging.info("H_clustering start");
        start = time.time();
        Z = hierarchy.average( dist );
        hierarchy_draw(Z, niks, CONFIG['name'], CONFIG['cut_level']);
        H_res = hierarchy_export(Z, niks, CONFIG['cut_level']);
        logging.info("%s sec." % (time.time() - start));


        #MST
        logging.info("G_clustering start");
        start = time.time();
        G = graph_mst(dist, niks, CONFIG['cut_level']);
        graph_draw(G, CONFIG['name']);
        G_res = graph_export(G);
        logging.info("%s sec." % (time.time() - start));


        #kmeans
        logging.info("K_clustering start");
        start = time.time();
            
        centroids = ( vq.kmeans( numpy.array(data), CONFIG['count_clusters']) )[0];
        K_res = kmeans_export(centroids, data, niks);
        logging.info("%s sec." % (time.time() - start));


        #сравнение результатов, рассчёт средних, сохранение результатов
        logging.info("Compare and saving results");
        start = time.time();
        similarity = compare_and_save(H_res, G_res, K_res, niks, cols, data, CONFIG['name'], recovery);
        logging.info("Similarity  hierarhy-MST: %.2f  hierarhy-kmeans: %.2f" % similarity);
        logging.info("%s sec." % (time.time() - start));


        logging.info("Time: %.4f sec" % ( time.time() - begin) );



    logging.info("End %s\n" % time.strftime('%H:%M:%S %d.%m.%Y'));
