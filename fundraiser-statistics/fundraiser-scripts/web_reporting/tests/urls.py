from django.conf.urls.defaults import *


urlpatterns = patterns('',
    (r'^$', 'tests.views.index'),
    (r'^build_test$', 'tests.views.test'),
    (r'^report/(?P<utm_campaign>[a-zA-Z0-9_]+)$', 'campaigns.views.show_report'),
    (r'^report/comment/(?P<utm_campaign>[a-zA-Z0-9_]+)$', 'tests.views.add_comment'),
)
