# settings.py
#from django.conf import settings
from django.core.management import setup_environ

import settings
setup_environ(settings)


from toolserver.models import *


#settings.configure(
#    DATABASES={
#        'default': {
#            'ENGINE': 'django.db.backends.mysql',
#            'NAME': 'myDatabase',
#            'USER': 'myUsername',
#            'PASSWORD': 'myPassword',
#            'HOST': '',
#            'PORT': ''
#
#        }
#    }
#)
#
#from django.db import models

