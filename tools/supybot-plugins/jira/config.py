###
# Copyright (c) 2007, River Tarnell
# All rights reserved.
#
#
###

import supybot.conf as conf
import supybot.registry as registry

def configure(advanced):
    # This will be called by supybot to configure this module.  advanced is
    # a bool that specifies whether the user identified himself as an advanced
    # user or not.  You should effect your configuration by manipulating the
    # registry as appropriate.
    from supybot.questions import expect, anything, something, yn
    conf.registerPlugin('jira', True)


jira = conf.registerPlugin('jira')
# This is where your configuration variables (if any) should go.  For example:
# conf.registerGlobalValue(Replag, 'someConfigVariableName',
#     registry.Boolean(False, """Help for someConfigVariableName."""))
conf.registerGlobalValue(jira, 'URL', registry.String("", 'URL to JIRA'))
conf.registerGlobalValue(jira, 'username', registry.String("", 'Username'))
conf.registerGlobalValue(jira, 'password', registry.String("", 'Password'))

# vim:set shiftwidth=4 tabstop=4 expandtab textwidth=79: