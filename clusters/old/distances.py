# -*- coding: utf-8 -*-

__author__="esemi"

from math import sqrt

#Расстояние должно быть тем меньше, чем более похожи списки

def simEvklid(list1, list2):
    """Возвращает Эвклидово расстояние между двумя списками"""

    return sqrt(sum([pow(list1[i]-list2[i], 2) for i in range(len(list1)) ]));



def simEvklidSquare(list1, list2):
    """Возвращает квадрат Эвклидовова расстояния между двумя списками"""
    
    return sum([pow(list1[i]-list2[i], 2) for i in range(len(list1)) ]);



def simPerson(list1, list2):
    """Возвращает величину, обратную коэфиценту корреляции Пирсона между двумя списками"""

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



def simSity(list1, list2):
    """Возвращает расстояние Хемминга (оноже Манхеттена или городских кварталов) между двумя списками"""

    return sum([ abs(list1[i]-list2[i]) for i in range(len(list1)) ]);



def simCheb(list1, list2):
    """Возвращает расстояние Чебышева"""

    return max([ abs(list1[i]-list2[i]) for i in range(len(list1)) ]);




if __name__ == '__main__':

    l1 = [5,4,3];
    l2 = [50,40,35];

    print "Эвклид: %f" % simEvklid(l1, l2), simEvklid(l1, l2);
    print "Эвклид квадрат: %f" % simEvklidSquare(l1, l2), simEvklidSquare(l1, l2);
    print "Пирсон: %f" % simPerson(l1, l2), simPerson(l1, l2);
    print "Хемминг: %f" % simSity(l1, l2), simSity(l1, l2);
    print "Чебышев: %f" % simCheb(l1, l2), simCheb(l1, l2);
