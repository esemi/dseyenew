#! /usr/bin/python
# -*- coding: utf-8 -*-

__author__ = "esemi"

from math import sqrt


def sim_evklid(props, p1, p2):
    """Возвращает эвклидово расстояние между оценками двух персон"""

    si = {};
    for item in props[p1]:
        if item in props[p2]:
            si[item] = 1;

    if len(si) == 0:
        # ниодной общей оценки
        return 0;
    else:
        # сумма квадратов разностей оценок
        return 1 / (1 + sum([pow(props[p1][item]-props[p2][item], 2)
                    for item in props[p1] if item in props[p2]]));





def sim_person(props, p1, p2):
    """Возвращает коэфицент корреляции Пирсона между оценками двух персон"""

    si = {};
    for item in props[p1]:
        if item in props[p2]:
            si[item] = 1;

    n = len(si);

    # ниодной общей оценки
    if n == 0: return 0;

    # сумма всех оценок
    sum1 = sum(props[p1][item] for item in si);
    sum2 = sum(props[p2][item] for item in si);

    # сумма квадратов всех оценок
    sum1qw = sum(pow(props[p1][item], 2) for item in si);
    sum2qw = sum(pow(props[p2][item], 2) for item in si);

    # сумма произведений
    pSum = sum(props[p1][item] * props[p2][item] for item in si);

    num = pSum-(sum1 * sum2 / n);
    den = sqrt((sum1qw - pow(sum1, 2) / n) * (sum2qw - pow(sum2, 2) / n));

    if den == 0: return 0;

    return num / den;






def sim_tanimoto(props, p1, p2):
    """Возвращает значение коэфицента Танимото между двумя персонами"""

    c = [item for item in props[p1] if item in props[p2]];
    return float(len(c)) / (len(props[p1]) + len(props[p2])-len(c));



def getMatches(props, person, count=3, simfunc=sim_person):
    """Возвращает несколько наиболее похожих сущностей"""
    scores = [("%.5f" % simfunc(critics, person, name), name) for name in props if name != person];
    scores.sort();
    scores.reverse();
    return scores[0:count];





def getRecomendations(props, person, count=3, simfunc=sim_person):
    """Выдать рекомендацию книги (ещё непрочитанной) конкретному человеку на основе взвешенного
    среднего оценок от других людей"""

    totals = {};
    simSums = {};

    for other in props:
        # самого с собой человека не сравниваем
        if person == other: continue;

        # коэф подобия
        sim = simfunc(props, person, other);

        # не работаем с отрицательными и нулевыми значениями схожести
        if sim <= 0: continue;

        for item in props[other]:
            # только то, что мы ещё не читали
            if (item in props[person]): continue;

            # коэф подобия * оценку
            totals.setdefault(item, 0);
            totals[item] += sim * props[other][item];

            # сумма коэф подобия
            simSums.setdefault(item, 0);
            simSums[item] += sim;

    ranks = [("%.5f" % (total / simSums[item]), item) for item, total in totals.items()];
    ranks.sort();
    ranks.reverse();
    return ranks[0:count]

    











if __name__ == "__main__":
    
    critics = {
    'author1': {'book1': 7.0, 'book2': 2.0, 'book3': 1.0, 'book4': 1.0},
    'author2': {'book1': 6.0, 'book2': 2.0, 'book4': 0.0, 'book6': 5.0},
    'author3': {'book1': 5.0, 'book3': 5.0, 'book5': 2.0, 'book7': 4.0},
    'author4': {'book1': 6.0, 'book2': 1.0, 'book3': 0.0, 'book6': 1.0},
    'author5': {'book1': 8.0, 'book3': 1.0, 'book4': 6.0, 'book6': 0.0},
    'author6': {'book1': 9.0, 'book2': 2.0, 'book4': 2.0, 'book7': 2.0},
    'author7': {'book1': 0.0, 'book2': 1.0, 'book5': 1.0, 'book7': 3.0},
    };

    curName = 'author5';
    curFunc = sim_person;

    for name in critics:
        if name != curName:
            print "%s: Tan %f Evk %f Per %f" % (name, sim_tanimoto(critics, curName, name), sim_evklid(critics, curName, name), sim_person(critics, curName, name));
                                                        
    print getMatches(critics, curName, 3, curFunc);

    print getRecomendations(critics, curName, 3, curFunc)
    

