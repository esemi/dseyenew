# -*- coding: utf-8 -*-

__author__ = "esemi"

import csv
import time
import gzip

import numpy as np


def save_cluster_result(path, list, cols, similarity):
    """Сохраняет итоговое разбиение на кластеры"""

    f = open(path, 'wb');
    
    print >> f, '%s' % time.strftime('%H:%M:%S %d.%m.%Y');
    print >> f, "hierarhy - mst: %.2f\nhierarhy - kmeans: %.2f" % similarity;
    print >> f, ';'.join(cols);

    for item in list:
        print >> f, '------CLUSTER------';
        print >> f, 'percent:%.2f' % item['percent'];
        print >> f, 'size:%d' % item['size'];
        print >> f, ','.join([i for i in item['players']]);
        print >> f, ';'.join(item['params']);

    f.close();

    

def save_clustermethod_result( path, lists ):
    """Сохраняет результат кластеризации в файл"""

    writer = csv.writer(open(path, 'wb'), delimiter=';')
    writer.writerows(lists);


def avg_data(data):
    """Вычисление среднего значения параметров"""
    
    matrix = np.array( data, 'f');
    matrix = np.average(matrix, axis=0);
    return matrix.tolist();

def prepare_data(data):
    """Приводит данные к единичному масштабу"""

    matrix = np.array( data, 'f');
    len_val = len(matrix[1, :]);
    recovery = [];
    for i in range(len_val):
        local_min = matrix[:, i].min();
        if(local_min != 0.0):
            matrix[:, i] -= local_min;

        local_max = matrix[:, i].max();
        if(local_max != 0.0):
            matrix[:, i] /= local_max;

        recovery.append([local_max, local_min]);           

    return matrix.tolist(), recovery;




def get_data(filepath):
    """ Возвращает очищенные и нормализованные данные """

    #получили данные
    source = csv.reader(gzip.open(filepath, 'rb'), delimiter=';')
    
    #отбросили лишние данные
    prep_data = [ [el for i, el in enumerate(row) if i not in (0, 2, 3, 6, 7, 8, 16, 18, 19, 20)] for row in source];

    #поделили на составные части
    colnames = prep_data[0][1:];
    colnames[1] = 'colony_count';

    niks = [row[0] for row in prep_data[1:]];

    data = [];
    for row in prep_data[1:]:
        row[2] = (0, row[2].count(',') + 1)[len(row[2].strip()) > 0]; #for colony_count
	row[4] = len(row[4]); #for ligue
        data.append(row[1:]);

    #привели строки к числам
    data = map(lambda x: [float(y) for y in x], data)

    #нормализовали, если надо
    data, recovery = prepare_data(data);

    return niks, colnames, data, recovery;






def recovery_data(data, recovery):
    """Восстановление исходной размерности данных"""

    list_of_params = avg_data(data);

    for i in range(len(list_of_params)):
        if(recovery[i][0] != 0.0):
            list_of_params[i] *= recovery[i][0];
        list_of_params[i] += recovery[i][1];
        
    return map( lambda x: "%.2f" % x, list_of_params );

if __name__ == "__main__":

    print "Start getdata";

    niks, cols, data, recovery = get_data("/home/esemi/www/csv/парус_main.csv.gz");

    data2 = recovery_data(data, recovery);
    print len(cols);
    print len(niks);
    print data2[2];
    print "End getdata"
    




