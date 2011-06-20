 /*
  **  mod_proctitle.c -- Apache proctitle module
  **
  **    $ apxs -c -i mod_proctitle.c
  **
  **    #   httpd.conf
  **    LoadModule proctitle_module modules/mod_proctitle.so
  */

#include <stdio.h>
#include <string.h>

#include "httpd.h"
#include "http_config.h"
#include "http_protocol.h"
#include "ap_config.h"

#define PROCTITLE_LEN 128

extern char *ap_server_argv0;

char *old_argv0;
char proctitle_buf[PROCTITLE_LEN + 1];

static void proctitle_child_init(apr_pool_t *p, server_rec *s)
{
    snprintf(ap_server_argv0, PROCTITLE_LEN, "%s :init-child", old_argv0);
}

static int proctitle_clear(request_rec * r)
{
    snprintf(ap_server_argv0, PROCTITLE_LEN, "%s :idle", old_argv0);
    return DECLINED;
}

static int proctitle_ft(request_rec * r)
{
    snprintf(ap_server_argv0, PROCTITLE_LEN, "%s :run %s [%s] %s",
	     old_argv0, r->connection->remote_ip, r->hostname,
	     r->the_request);
    return DECLINED;
}

static void proctitle_register_hooks(apr_pool_t * p)
{
    ap_hook_child_init(proctitle_child_init, NULL, NULL, APR_HOOK_FIRST);
    ap_hook_translate_name(proctitle_ft, NULL, NULL, APR_HOOK_FIRST);
    ap_hook_log_transaction(proctitle_clear, NULL, NULL, APR_HOOK_LAST);
    old_argv0 = strdup(ap_server_argv0);
}

 /* Dispatch list for API hooks */
module AP_MODULE_DECLARE_DATA proctitle_module = {
    STANDARD20_MODULE_STUFF,
    NULL,			/* create per-dir    config structures */
    NULL,			/* merge  per-dir    config structures */
    NULL,			/* create per-server config structures */
    NULL,			/* merge  per-server config structures */
    NULL,			/* table of config file commands       */
    proctitle_register_hooks	/* register hooks                      */
};
