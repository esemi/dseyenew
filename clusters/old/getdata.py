#! /usr/bin/python
# -*- coding: utf-8 -*-

__author__="esemi"


import feedparser
import re

def getWordsCount(url):
    """Возвращает заголово и словарь слов со счётчиками по РСС каналу"""

    source=feedparser.parse(url);
    wc={};


    for e in source.entries:
        #получаем текст новости (заголовок или описание)
        if 'summary' in e :
            summary=e.summary;
        else:
            summary=e.description;
        
        html=e.title.encode('utf8', 'replace') + ' ' + summary.encode('utf8', 'replace');

        #получаем список слов и считаем словарь
        words=getWords(html);

        for word in words:
            wc.setdefault(word, 0);
            wc[word]+=1;
   
    return source.feed.link, wc;


def getWords(html):
    """Возвращает список слов из очищенной от тегов строки"""

    # убираем теги и спецсимволы
    txt=re.compile(u'<[^>]+>|&[a-zA-Z]+;', re.UNICODE).sub(' ', unicode(html,'utf8'));

    # убираем всё кроме букв обоих алфавитов и пробелов
    txt=re.compile(u'[^а-яА-ЯёЁa-zA-Z\s]+', re.UNICODE).sub(' ', txt);

    words=re.compile(u'\s+', re.UNICODE).split(txt);
    
    return [ word.lower()  for word in words if len(word) > 3];








if __name__ == "__main__":

    apcount={}    # список слов количество блогов, в которых оно упоминается чаще одного раза
    wordcounts={} # словари слов со счётчиками по тайтлам блогов
    feedlist=0    # количество блогов

    # перебираем всех фидов
    for line in file('data/habrlist.txt'):

        feedlist+=1;

        title,wc = getWordsCount(line);

        wordcounts[title]=wc;
        
        print title.encode('utf-8');

        for word,count in wc.items():
            apcount.setdefault(word, 0);
            if count>1 :
                apcount[word]+=1;

    #считаем слова
    wordList=[];
    for word,countBlogs in apcount.items():
        frac=float(countBlogs)/float(feedlist);
        if (frac>0.1) and (frac < 0.9):
            wordList.append(word);
            print "%s - %d - %f" % (word.encode('utf-8'), countBlogs, frac);


    #пишем итоги в файл
    print len(wordList);
    out=file('data/rssdata.txt', 'w');
    for word in wordList:
        out.write('\t%s' % word.encode('utf-8') )

    out.write('\n')

    for blog, wc in wordcounts.items():
        out.write(blog.encode("utf-8"))
        for word in wordList:
            if word in wc:
                out.write('\t%d' % wc[word] );
            else:
                out.write('\t0')
        out.write('\n')
