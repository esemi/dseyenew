#! /usr/bin/env python3
# -*- coding: utf-8 -*-

import os

from fabric.api import env, run, put, cd
from fabric.contrib.files import exists

env.user = 'dseye'

REMOTE_PATH = os.path.join('/home', env.user)
FOLDERS = ('www', 'app', 'conf')
LOCAL_PATH = os.path.dirname(__file__)
APP_PATH = os.path.join(REMOTE_PATH, 'app')
LOGS_PATH = os.path.join(APP_PATH, 'logs')
SESSION_PATH = os.path.join(APP_PATH, 'sessions')
CACHE_PATH = os.path.join(APP_PATH, 'cache')
ZEND_PATH = os.path.join(REMOTE_PATH, 'Zend')


def tests():
	pass


def deploy():
	if not exists(ZEND_PATH):
		run('mkdir -p %s' % ZEND_PATH)
		put(os.path.join(LOCAL_PATH, 'conf', 'Zend.tar.gz'), ZEND_PATH)
		with cd(ZEND_PATH):
			run('tar -xzf Zend.tar.gz')

	if not exists(APP_PATH):
		run('mkdir -p %s' % APP_PATH)

	if not exists(LOGS_PATH):
		run('mkdir -p %s' % LOGS_PATH)
		run('chgrp www-data %s' % LOGS_PATH)
	# run('chmod 774 -R %s' % LOGS_PATH)

	if not exists(CACHE_PATH):
		run('mkdir -p %s' % CACHE_PATH)
		run('chgrp www-data %s' % CACHE_PATH)

	if not exists(SESSION_PATH):
		run('mkdir -p %s' % SESSION_PATH)
		run('chgrp www-data %s' % SESSION_PATH)

	for folder in FOLDERS:
		put(os.path.join(LOCAL_PATH, folder), APP_PATH)

	run('crontab %s' % os.path.join(APP_PATH, 'conf', 'crontab'))
