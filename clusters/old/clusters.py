#! /usr/bin/python
# -*- coding: utf-8 -*-

__author__="esemi"

from PIL import Image, ImageDraw, ImageFont
from math import sqrt
import sys
reload(sys)
sys.setdefaultencoding( "utf8" )

def readfile(filename):
    """Читает данные из файла частотности упоминания слов в рсс-каналах"""

    lines=[line for line in file('data/'+filename)];

    colnames=lines[0].strip().split('\t');
   
    rownames=[];
    data=[];

    for line in lines[1:]:
        p=line.strip().split('\t');
        #название строки
        rownames.append(p[0]);
        #основные данные
        data.append([ float(x) for x in p[1:] ]);

    return rownames,colnames,data;




def simEvklid(list1, list2):
    """Возвращает величину, обратную Эвклидовову расстоянию между двумя векторами"""

    return 1 - ( 1 / (1 + sum([pow(list1[i]-list2[i], 2) for i in range(len(list1)) ])) );


def simPerson(list1, list2):
    """Возвращает обратную величину коэфицента корреляции Пирсона между двумя векторами"""

    # сумма всех оценок
    sum1 = sum(list1);
    sum2 = sum(list2);

    # сумма квадратов всех оценок
    sum1qw = sum([ pow(item, 2) for item in list1 ]);
    sum2qw = sum([ pow(item, 2) for item in list2 ]);

    # сумма произведений
    pSum = sum([ list1[i]*list2[i] for i in range(len(list1)) ]);

    num = pSum-(sum1 * sum2 / len(list1));
    den = sqrt((sum1qw - pow(sum1, 2) / len(list1)) * (sum2qw - pow(sum2, 2) / len(list1)));

    if den == 0: return 0;

    return 1 - (num / den);







# биполярный кластер

class Bicluster:
    def __init__(self, vec, left=None, right=None, distance=0.0, id=None):
        self.left = left
        self.right = right
        self.vec = vec
        self.id = id
        self.distance = distance


def hcluster(rows, distance=simPerson):
    """Производим иерархическую восходящую кластеризацию"""
    
    distances = {}; # кеш расстояний
    currentclustid = -1;

    # clusters are initially just rows
    clust = [Bicluster(rows[i], id=i) for i in range(len(rows))]


    while len(clust) > 1:
        lowestpair = (0,1); # координаты пары кластеров с минимальной дистанцией
        closest = distance(clust[0].vec, clust[1].vec); # минимальная найденная дистанция между парами кластеров

        # перебор всех пар кластеров для поиска минимальной дистанции
        for i in range(len(clust)):
            for j in range(i+1, len(clust)):

                if (clust[i].id, clust[j].id) not in distances:
                    distances[(clust[i].id, clust[j].id)] = distance(clust[i].vec, clust[j].vec);

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

        # cluster ids that weren't in the original set are negative
        currentclustid -= 1
        del clust[lowestpair[1]]
        del clust[lowestpair[0]]
        clust.append(newcluster)

    return clust[0]









def printclust(clust, labels=None, n=0):
    # indent to make a hierarchy layout
    for i in range(n):
        print ' ',
        if clust.id < 0:
            # negative ide means that this is a branch
            print '-'
        else:
            # positive id means that this is an endpoint
            if labels == None:
                print clust.id
            else:
                print labels[clust.id]

    # now print the right and left branches
    if clust.left != None:
        printclust(clust.left, labels=labels, n=n+1)
        
    if clust.right != None:
        printclust(clust.right, labels=labels, n=n+1)




# calculate the height of a cluster
def getheight(clust):
    # is this an endpoint? end recursion if so and height is just 1
    if clust.left == None and clust.right == None:
        return 1

    # otherwise the height is equivalent to the height of the 2 branches added
    # together
    return getheight(clust.left) + getheight(clust.right)


# calculate the depth of a cluster node
def getdepth(clust):
    # the distance of an endpoint is 0.0
    if clust.left == None and clust.right == None:
        return 0

    # the distance of a branch is the greater of its two sides plus its own
    # distance
    return max(getdepth(clust.left), getdepth(clust.right)) + clust.distance



# Draw a hierarchical clustering dendogram
def drawdendogram(clust, labels, filename='clusters.jpg'):
    # height and width
    h = getheight(clust)*20
    w = 6000
    depth = getdepth(clust)

    # width is fixed, so scale distances accordingly
    scaling = float(w-150) / depth

    # create a new image ith a white background
    img = Image.new('RGB', (w,h), (255, 255, 255))
    draw = ImageDraw.Draw(img)

    draw.line((0, h/2, 10, h/2), fill=(255,0,0))

    # draw the first node
    drawnode(draw, clust, 10, (h/2), scaling, labels)
    img.save('data/'+filename, 'JPEG')



# take a cluster and it location and draw lines from it to child nodes
def drawnode(draw, clust, x, y, scaling, labels):

    #print clust.id, clust.left, clust.right;

    if clust.id < 0:
        h1 = getheight(clust.left) * 20
        h2 = getheight(clust.right) * 20
        top = y - (h1 + h2) / 2
        bottom = y + (h1 + h2) / 2

        # line length
        ll = clust.distance * scaling

        # vertical line from this cluster to children
        draw.line((x, top+h1/2, x, bottom-h2/2), fill=(255,0,0))

        # horizontal line to left item
        draw.line((x, top+h1/2, x+ll, top+h1/2), fill=(255,0,0))

        # horizontal line to right item
        draw.line((x, bottom-h2/2, x+ll, bottom-h2/2), fill=(255,0,0))

        # call the function recursively to draw the left and right nodes
        drawnode(draw, clust.left, x+ll, top+h1/2, scaling, labels)
        drawnode(draw, clust.right, x+ll, bottom-h2/2, scaling, labels)
    else:
        # if this is an endpoint, draw the item label
        fontPath = "components/FreeMono.ttf"
        font = ImageFont.truetype ( fontPath, 12 )
        draw.text((x+5, y-7), unicode(labels[clust.id], 'utf-8'), fill='black', font=font )




def rotatematrix(data):
    newdata = []
    for i in range(len(data[0])):
        newrow = [data[j][i] for j in range(len(data))]
        newdata.append(newrow)
    return newdata



if __name__ == "__main__":

    print "Start";

    blogname,words,data = readfile('rssdata.txt');
    rotateData=rotatematrix(data);

    clust=hcluster(data);
    drawdendogram(clust, blogname , 'clustersBlogPerson.jpg');

    #clust=hcluster(data, distance=simEvklid);
    #drawdendogram(clust, blogname , 'clustersBlogEvklid.jpg');



    clust=hcluster(rotateData);
    drawdendogram(clust, words , 'clustersWordsPerson.jpg');

    #clust=hcluster(rotateData, distance=simEvklid);
    #drawdendogram(clust, words , 'clustersWordsEvklid.jpg');
    
