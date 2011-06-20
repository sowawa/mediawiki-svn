# This also imports the include function
from django.conf.urls.defaults import *
from django.conf import settings

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    (r'^media/(?P<path>.*)$', 'django.views.static.serve', {'document_root': settings.MEDIA_ROOT }),
    (r'^$', 'views.index'),
    (r'^campaigns/', include('campaigns.urls')),
    (r'^tests/', include('tests.urls')),
    (r'^live_stats/', include('live_results.urls')),
    (r'^LML/', include('LML.urls')),
    (r'^admin/', include(admin.site.urls)),
)
