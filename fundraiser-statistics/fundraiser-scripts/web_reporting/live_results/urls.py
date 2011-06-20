from django.conf.urls.defaults import *


urlpatterns = patterns('',
    (r'^$', 'live_results.views.index'),
)
