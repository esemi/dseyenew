# -*- coding: utf-8 -*-

__author__="esemi"

import logging;

# биполярный кластер
class Bicluster:
    def __init__(self, vec, left=None, right=None, distance=0.0, id=None):
        self.left = left;
        self.right = right;
        self.vec = vec;
        self.id = id;
        self.distance = distance;
        self.sum = [];

        if( right != None and left != None ):
            self.sum = right.sum + left.sum;
            if( right.id > 0 ):
                self.sum.append( right.id );
            if( left.id > 0 ):
                self.sum.append( left.id );               







def hcluster(rows, distance, limit=1):
    """Производим иерархическую восходящую кластеризацию"""
    
    distances = {}; # кеш расстояний
    cache_miss = 0.0; # промахи кеша
    cache_hit = 0.0; # попадания кеша
    
    currentclustid = -1;

    clust = [Bicluster(rows[i], id=i) for i in range(len(rows))]

    while len(clust) > limit:
        lowestpair = (0,1); # координаты пары кластеров с минимальной дистанцией
        closest = distance(clust[0].vec, clust[1].vec); 

        # перебор всех пар кластеров для поиска минимальной дистанции
        for i in range(len(clust)):
            for j in range(i+1, len(clust)):

                #кеш дистанций
                if (clust[i].id, clust[j].id) not in distances:
                    distances[(clust[i].id, clust[j].id)] = distance(clust[i].vec, clust[j].vec);
                    cache_miss += 1.0;
                else:
                    cache_hit += 1.0;

                d = distances[(clust[i].id, clust[j].id)];

                if d < closest:
                    closest = d;
                    lowestpair = (i, j);

        # находим средний вектор для новообразованного кластера
        mergevec = [(clust[lowestpair[0]].vec[i] + clust[lowestpair[1]].vec[i]) / 2.0
                       for i in range(len(clust[0].vec))];
                       
        # создаём кластер
        newcluster = Bicluster(mergevec, left=clust[lowestpair[0]],
                               right=clust[lowestpair[1]],
                               distance=closest, id=currentclustid)
        
        currentclustid -= 1
        del clust[lowestpair[1]]
        del clust[lowestpair[0]]
        clust.append(newcluster)

        logging.debug("Кластеров: %d; Кеш (hit/miss): %f; Дистанция: %f" % (len(clust), cache_hit/cache_miss, closest));

    return clust[0]



def rotatematrix(data):
    newdata = []
    for i in range(len(data[0])):
        newrow = [data[j][i] for j in range(len(data))]
        newdata.append(newrow)
    return newdata






def get_few_highest_clust(clust, limit):
    """Возвращает указанное количество верхних кластеров в иерархическом дереве"""

    res = [(clust.id, clust)];
    while (len(res) < limit):
        res.sort();
        if( res[0][0] >= 0 ):
            break;
        tmp = res.pop(0);
        res.append( (tmp[1].right.id, tmp[1].right) );
        res.append( (tmp[1].left.id, tmp[1].left) );

    out = {};
    for item in res:
        if( item[0] >= 0 ):
            out[item[0]] = [item[1].id];
        else:
            out[item[0]] = item[1].sum;

    return out;
