
/*
 * Copyright (C) 2010 Valery Kholodkov
 *
 * NOTE: Some small fragments have been copied from original nginx log module due to exports problem.
 */


#include <ngx_config.h>
#include <ngx_core.h>
#include <ngx_http.h>
#include <nginx.h>

#if defined nginx_version && nginx_version >= 8021
typedef ngx_addr_t ngx_udplog_addr_t;
#else
typedef ngx_peer_addr_t ngx_udplog_addr_t;
#endif

typedef struct ngx_http_log_op_s  ngx_http_log_op_t;

typedef u_char *(*ngx_http_log_op_run_pt) (ngx_http_request_t *r, u_char *buf,
    ngx_http_log_op_t *op);

typedef size_t (*ngx_http_log_op_getlen_pt) (ngx_http_request_t *r,
    uintptr_t data);


struct ngx_http_log_op_s {
    size_t                      len;
    ngx_http_log_op_getlen_pt   getlen;
    ngx_http_log_op_run_pt      run;
    uintptr_t                   data;
};

typedef struct {
    ngx_str_t                   name;
#if defined nginx_version && nginx_version >= 7018
    ngx_array_t                *flushes;
#endif
    ngx_array_t                *ops;        /* array of ngx_http_log_op_t */
} ngx_http_log_fmt_t;

typedef struct {
    ngx_array_t                 formats;    /* array of ngx_http_log_fmt_t */
    ngx_uint_t                  combined_used; /* unsigned  combined_used:1 */
} ngx_http_log_main_conf_t;

typedef struct {
    ngx_udplog_addr_t                 peer_addr;
    ngx_udp_connection_t      *udp_connection;
} ngx_udp_endpoint_t;

typedef struct {
    ngx_udp_endpoint_t       *endpoint;
    ngx_http_log_fmt_t       *format;
    ngx_uint_t                facility;
    ngx_uint_t                severity;
} ngx_http_udplog_t;

typedef struct {
    ngx_array_t                *endpoints;
} ngx_http_udplog_main_conf_t;

typedef struct {
    ngx_array_t                *logs;       /* array of ngx_http_udplog_t */
    unsigned                    off;
} ngx_http_udplog_conf_t;

ngx_int_t ngx_udp_connect(ngx_udp_connection_t *uc);

static void ngx_udplogger_cleanup(void *data);
static ngx_int_t ngx_http_udplogger_send(ngx_udp_endpoint_t *l, u_char *buf, size_t len);

static void *ngx_http_udplog_create_main_conf(ngx_conf_t *cf);
static void *ngx_http_udplog_create_loc_conf(ngx_conf_t *cf);
static char *ngx_http_udplog_merge_loc_conf(ngx_conf_t *cf, void *parent,
    void *child);

static char *ngx_http_udplog_set_log(ngx_conf_t *cf, ngx_command_t *cmd, void *conf);

static ngx_int_t ngx_http_udplog_init(ngx_conf_t *cf);
static ngx_int_t ngx_http_udplog_add_variables(ngx_conf_t *cf);
static ngx_int_t ngx_http_udplog_time_variable(ngx_http_request_t *r,
    ngx_http_variable_value_t *v, uintptr_t data);
static ngx_int_t ngx_http_udplog_sequence_variable(ngx_http_request_t *r,
    ngx_http_variable_value_t *v, uintptr_t data);

static ngx_http_variable_t  ngx_http_udplog_variables[] = {
	{ ngx_string("udplog_time"), NULL, ngx_http_udplog_time_variable, 0,
          NGX_HTTP_VAR_NOCACHEABLE|NGX_HTTP_VAR_NOHASH, 0 },
	{ ngx_string("udplog_sequence"), NULL, ngx_http_udplog_sequence_variable, 0,
          NGX_HTTP_VAR_NOCACHEABLE|NGX_HTTP_VAR_NOHASH, 0 },

	{ ngx_null_string, NULL, NULL, 0, 0, 0 }
};

static ngx_command_t  ngx_http_udplog_commands[] = {

    { ngx_string("access_udplog"),
      NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF|NGX_HTTP_LIF_CONF
                        |NGX_HTTP_LMT_CONF|NGX_CONF_TAKE123,
      ngx_http_udplog_set_log,
      NGX_HTTP_LOC_CONF_OFFSET,
      0,
      NULL },

      ngx_null_command
};


static ngx_http_module_t  ngx_http_udplog_module_ctx = {
    ngx_http_udplog_add_variables,          /* preconfiguration */
    ngx_http_udplog_init,                  /* postconfiguration */

    ngx_http_udplog_create_main_conf,      /* create main configuration */
    NULL,                                  /* init main configuration */

    NULL,                                  /* create server configuration */
    NULL,                                  /* merge server configuration */

    ngx_http_udplog_create_loc_conf,       /* create location configration */
    ngx_http_udplog_merge_loc_conf         /* merge location configration */
};

extern ngx_module_t  ngx_http_log_module;

ngx_module_t  ngx_http_udplog_module = {
    NGX_MODULE_V1,
    &ngx_http_udplog_module_ctx,           /* module context */
    ngx_http_udplog_commands,              /* module directives */
    NGX_HTTP_MODULE,                       /* module type */
    NULL,                                  /* init master */
    NULL,                                  /* init module */
    NULL,                                  /* init process */
    NULL,                                  /* init thread */
    NULL,                                  /* exit thread */
    NULL,                                  /* exit process */
    NULL,                                  /* exit master */
    NGX_MODULE_V1_PADDING
};

static ngx_int_t
ngx_http_udplog_time_variable(ngx_http_request_t *r,
    ngx_http_variable_value_t *v, uintptr_t data)
{
    size_t                    len;
    ngx_time_t                *time;
    ngx_tm_t                  tm;

    time = ngx_timeofday();
    ngx_gmtime(time->sec, &tm);

    len = sizeof("2011-06-28T00:00:00.000");
#if defined nginx_version && nginx_version >= 7003
    v->data = ngx_pnalloc(r->pool, len);
#else
    v->data = ngx_palloc(r->pool, len);
#endif
    if (v->data == NULL) {
        return NGX_ERROR;
    }

    v->len = ngx_sprintf(v->data,
                      "%4d-%02d-%02dT%02d:%02d:%02d.%03M",
                       tm.ngx_tm_year,
                       tm.ngx_tm_mon,
                       tm.ngx_tm_mday,
                       tm.ngx_tm_hour,
                       tm.ngx_tm_min,
                       tm.ngx_tm_sec,
                       time->msec) - v->data;

    v->valid = 1;
    v->no_cacheable = 0;
    v->not_found = 0;

    return NGX_OK;
}

static ngx_int_t
ngx_http_udplog_sequence_variable(ngx_http_request_t *r,
    ngx_http_variable_value_t *v, uintptr_t data)
{
    static long int sequence_number = 0;

    sequence_number++;

#if defined nginx_version && nginx_version >= 7003
    v->data = ngx_pnalloc(r->pool, sizeof(sequence_number));
#else
    v->data = ngx_palloc(r->pool, sizeof(sequence_number));
#endif
    if (v->data == NULL) {
        return NGX_ERROR;
    }

    v->len = ngx_sprintf(v->data, "%l", sequence_number) - v->data;
    v->valid = 1;
    v->no_cacheable = 0;
    v->not_found = 0;

    return NGX_OK;
}

static ngx_int_t
ngx_http_udplog_add_variables(ngx_conf_t *cf)
{
    ngx_http_variable_t  *var, *v;

    for (v = ngx_http_udplog_variables; v->name.len; v++) {
        var = ngx_http_add_variable(cf, &v->name, v->flags);
        if (var == NULL) {
            return NGX_ERROR;
        }

        var->get_handler = v->get_handler;
        var->data = v->data;
    }

    return NGX_OK;
}

ngx_int_t
ngx_http_udplog_handler(ngx_http_request_t *r)
{
    u_char                   *line, *p;
    size_t                    len;
    ngx_uint_t                i, l;
    ngx_http_udplog_t        *log;
    ngx_http_log_op_t        *op;
    ngx_http_udplog_conf_t   *ulcf;
    ngx_log_debug0(NGX_LOG_DEBUG_HTTP, r->connection->log, 0,
                   "http udplog handler");

    ulcf = ngx_http_get_module_loc_conf(r, ngx_http_udplog_module);

    if(ulcf->off) {
        return NGX_OK;
    }

    log = ulcf->logs->elts;
    for (l = 0; l < ulcf->logs->nelts; l++) {

#if defined nginx_version && nginx_version >= 7018
        ngx_http_script_flush_no_cacheable_variables(r, log[l].format->flushes);
#endif

        len = 0;
        op = log[l].format->ops->elts;
        for (i = 0; i < log[l].format->ops->nelts; i++) {
            if (op[i].len == 0) {
                len += op[i].getlen(r, op[i].data);

            } else {
                len += op[i].len;
            }
        }

	len += NGX_LINEFEED_SIZE;
#if defined nginx_version && nginx_version >= 7003
        line = ngx_pnalloc(r->pool, len);
#else
        line = ngx_palloc(r->pool, len);
#endif
        if (line == NULL) {
            return NGX_ERROR;
        }

	p = line;
        for (i = 0; i < log[l].format->ops->nelts; i++) {
            p = op[i].run(r, p, &op[i]);
        }
	ngx_linefeed(p);

        ngx_http_udplogger_send(log[l].endpoint, line, p - line);
    }

    return NGX_OK;
}

static ngx_int_t ngx_udplog_init_endpoint(ngx_conf_t *cf, ngx_udp_endpoint_t *endpoint) {
    ngx_pool_cleanup_t    *cln;
    ngx_udp_connection_t  *uc;

    cln = ngx_pool_cleanup_add(cf->pool, 0);
    if(cln == NULL) {
        return NGX_ERROR;
    }

    cln->handler = ngx_udplogger_cleanup;
    cln->data = endpoint;

    uc = ngx_calloc(sizeof(ngx_udp_connection_t), cf->log);
    if (uc == NULL) {
        return NGX_ERROR;
    }

    endpoint->udp_connection = uc;

    uc->sockaddr = endpoint->peer_addr.sockaddr;
    uc->socklen = endpoint->peer_addr.socklen;
    uc->server = endpoint->peer_addr.name;
#if defined nginx_version && nginx_version >= 7054
    uc->log = &cf->cycle->new_log;
#else
    uc->log = cf->cycle->new_log;
#endif

    return NGX_OK;
}

static void
ngx_udplogger_cleanup(void *data)
{
    ngx_udp_endpoint_t  *e = data;

    ngx_log_debug0(NGX_LOG_DEBUG_CORE, ngx_cycle->log, 0,
                   "cleanup udplogger");

    if(e->udp_connection) {
        if(e->udp_connection->connection) {
            ngx_close_connection(e->udp_connection->connection);
        }

        ngx_free(e->udp_connection);
    }
}

static void ngx_http_udplogger_dummy_handler(ngx_event_t *ev)
{
}

static ngx_int_t
ngx_http_udplogger_send(ngx_udp_endpoint_t *l, u_char *buf, size_t len)
{
    ssize_t                n;
    ngx_udp_connection_t  *uc;

    uc = l->udp_connection;

    if (uc->connection == NULL) {
        if(ngx_udp_connect(uc) != NGX_OK) {
            return NGX_ERROR;
        }

        uc->connection->data = l;
        uc->connection->read->handler = ngx_http_udplogger_dummy_handler;
        uc->connection->read->resolver = 0;
    }

    n = ngx_send(uc->connection, buf, len);

    if (n == -1) {
        return NGX_ERROR;
    }

    if ((size_t) n != (size_t) len) {
        ngx_log_error(NGX_LOG_CRIT, uc->log, 0, "send() incomplete");
        return NGX_ERROR;
    }

    return NGX_OK;
}

static void *
ngx_http_udplog_create_main_conf(ngx_conf_t *cf)
{
    ngx_http_udplog_main_conf_t  *conf;

    conf = ngx_pcalloc(cf->pool, sizeof(ngx_http_udplog_main_conf_t));
    if (conf == NULL) {
        return NGX_CONF_ERROR;
    }

    return conf;
}

static void *
ngx_http_udplog_create_loc_conf(ngx_conf_t *cf)
{
    ngx_http_udplog_conf_t  *conf;

    conf = ngx_pcalloc(cf->pool, sizeof(ngx_http_udplog_conf_t));
    if (conf == NULL) {
        return NGX_CONF_ERROR;
    }

    return conf;
}

static char *
ngx_http_udplog_merge_loc_conf(ngx_conf_t *cf, void *parent, void *child)
{
    ngx_http_udplog_conf_t *prev = parent;
    ngx_http_udplog_conf_t *conf = child;

    ngx_http_udplog_t         *log;
    ngx_http_log_fmt_t        *fmt;
    ngx_http_log_main_conf_t  *lmcf;

    if(conf->logs || conf->off) {
        return NGX_CONF_OK;
    }

    conf->logs = prev->logs;
    conf->off = prev->off;

    if(conf->logs || conf->off) {
        return NGX_CONF_OK;
    }

    conf->logs = ngx_array_create(cf->pool, 2, sizeof(ngx_http_udplog_t));
    if(conf->logs == NULL) {
        return NGX_CONF_ERROR;
    }

    log = ngx_array_push(conf->logs);
    if(log == NULL) {
        return NGX_CONF_ERROR;
    }

    lmcf = ngx_http_conf_get_module_main_conf(cf, ngx_http_log_module);
    fmt = lmcf->formats.elts;

    /* the default "combined" format */
    log->format = &fmt[0];
    lmcf->combined_used = 1;

    return NGX_CONF_OK;
}

static ngx_udp_endpoint_t *
ngx_http_udplog_add_endpoint(ngx_conf_t *cf, ngx_udplog_addr_t *peer_addr)
{
    ngx_http_udplog_main_conf_t    *umcf;
    ngx_udp_endpoint_t             *endpoint;

    umcf = ngx_http_conf_get_module_main_conf(cf, ngx_http_udplog_module);

    if(umcf->endpoints == NULL) {
        umcf->endpoints = ngx_array_create(cf->pool, 2, sizeof(ngx_udp_endpoint_t));
        if (umcf->endpoints == NULL) {
            return NULL;
        }
    }

    endpoint = ngx_array_push(umcf->endpoints);
    if (endpoint == NULL) {
        return NULL;
    }

    endpoint->peer_addr = *peer_addr;

    return endpoint;
}

static char *
ngx_http_udplog_set_log(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    ngx_http_udplog_conf_t      *ulcf = conf;

    ngx_uint_t                  i;
    ngx_str_t                   *value, name;
    ngx_http_udplog_t           *log;
    ngx_http_log_fmt_t          *fmt;
    ngx_http_log_main_conf_t    *lmcf;
    ngx_url_t                   u;
    u_char                      *host, *last, *port;
    size_t                      len;
    ngx_int_t                   n;

    value = cf->args->elts;

    if (ngx_strcmp(value[1].data, "off") == 0) {
        ulcf->off = 1;
        return NGX_CONF_OK;
    }

    if (ulcf->logs == NULL) {
        ulcf->logs = ngx_array_create(cf->pool, 2, sizeof(ngx_http_udplog_t));
        if (ulcf->logs == NULL) {
            return NGX_CONF_ERROR;
        }
    }

    lmcf = ngx_http_conf_get_module_main_conf(cf, ngx_http_log_module);

    if(lmcf == NULL) {
        ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                           "udplog module requires log module to be compiled in");
        return NGX_CONF_ERROR;
    }

    log = ngx_array_push(ulcf->logs);
    if (log == NULL) {
        return NGX_CONF_ERROR;
    }

    ngx_memzero(log, sizeof(ngx_http_udplog_t));

    ngx_memzero(&u, sizeof(ngx_url_t));

    host = value[1].data;
    last = host + value[1].len;
    port = ngx_strlchr(host, last, ':');
    u.default_port = (in_port_t) 514;
    if (port) {
        port++;
        len = last - port;
        if (len == 0) {
            return NGX_CONF_ERROR;
        }

        n = ngx_atoi(port, len);

        if (n < 1 || n > 65536) {
            return NGX_CONF_ERROR;
        }

        u.port = (in_port_t) n;
        last = port - 1;
    } else {
        u.port = u.default_port;
    }

    len = last - host;

    if (len == 0) {
        return NGX_CONF_ERROR;
    }

    u.host.len = len;
    u.host.data = host;

    if(ngx_inet_resolve_host(cf->pool, &u) != NGX_OK) {
        ngx_conf_log_error(NGX_LOG_EMERG, cf, 0, "%V: %s", &u.host, u.err);
        return NGX_CONF_ERROR;
    }

    log->endpoint = ngx_http_udplog_add_endpoint(cf, &u.addrs[0]);

    if(log->endpoint == NULL) {
        return NGX_CONF_ERROR;
    }

    if (cf->args->nelts >= 3) {
        name = value[2];

        if (ngx_strcmp(name.data, "combined") == 0) {
            lmcf->combined_used = 1;
        }
    } else {
        name.len = sizeof("combined") - 1;
        name.data = (u_char *) "combined";
        lmcf->combined_used = 1;
    }

    fmt = lmcf->formats.elts;
    for (i = 0; i < lmcf->formats.nelts; i++) {
        if (fmt[i].name.len == name.len
            && ngx_strcasecmp(fmt[i].name.data, name.data) == 0)
        {
            log->format = &fmt[i];
            return NGX_CONF_OK;
        }
    }

    ngx_conf_log_error(NGX_LOG_EMERG, cf, 0,
                       "unknown log format \"%V\"", &name);
    return NGX_CONF_ERROR;

}

static ngx_int_t
ngx_http_udplog_init(ngx_conf_t *cf)
{
    ngx_int_t                     rc;
    ngx_uint_t                    i;
    ngx_http_core_main_conf_t    *cmcf;
    ngx_http_udplog_main_conf_t  *umcf;
    ngx_http_handler_pt          *h;
    ngx_udp_endpoint_t           *e;

    umcf = ngx_http_conf_get_module_main_conf(cf, ngx_http_udplog_module);

    if(umcf->endpoints != NULL) {
        e = umcf->endpoints->elts;
        for(i = 0;i < umcf->endpoints->nelts;i++) {
            rc = ngx_udplog_init_endpoint(cf, e + i);

            if(rc != NGX_OK) {
                return NGX_ERROR;
            }
        }

        cmcf = ngx_http_conf_get_module_main_conf(cf, ngx_http_core_module);

        h = ngx_array_push(&cmcf->phases[NGX_HTTP_LOG_PHASE].handlers);
        if (h == NULL) {
            return NGX_ERROR;
        }

        *h = ngx_http_udplog_handler;
    }

    return NGX_OK;
}
