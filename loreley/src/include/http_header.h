/* Loreley: Lightweight HTTP reverse-proxy.                              */
/* http_header: header processing implementation.			*/
/* Copyright (c) 2005, 2006 River Tarnell <river@attenuate.org>.        */
/*
 * Permission is granted to anyone to use this software for any purpose,
 * including commercial applications, and to alter it and redistribute it
 * freely. This software is provided 'as-is', without any express or implied
 * warranty.
 */

/* @(#) $Id$ */

#ifndef WHTTP_HEADER
#define WHTTP_HEADER

#include "http.h"
#include "loreley.h"
#include "net.h"
#include "flowio.h"

/*
 * Do NOT change these values, they are used in the UDP
 * log packets.
 */
#define REQTYPE_GET	0
#define REQTYPE_POST	1
#define REQTYPE_HEAD	2
#define REQTYPE_TRACE	3
#define REQTYPE_OPTIONS	4
#define REQTYPE_PURGE	5
#define REQTYPE_INVALID	-1

#define HDR_BUFSZ	256

extern struct request_type {
	const char *name;
	int len;
	int type;
} supported_reqtypes[];

/*
 * A single header.  Usually stored inside a header_list.  For speed,
 * we assume the length of one header line is no more than HDX_BUFSZ-1
 * characters, and pre-allocate a buffer of that size.  In case the
 * header is longer, hr_buffer is ignored, and a new buffer is allocated
 * from the pt_allocator.  hr_free==true indicated that the buffer should
 * be freed on destruction.
 */
struct header : freelist_allocator<header> {
	/*
	 * Construct a new header from the given name and value, which need
	 * not be null terminated.
	 */
	header( char const *name, size_t namelen,
		char const *value, size_t valuelen);

	~header();
	header(header const &);
	header& operator= (header const &);

	/*
	 * Assign new values to this header.
	 */
	void	assign( char const *name, size_t namelen,
			char const *value, size_t valuelen);

	/*
	 * Destructively assign the contents of other to *this.  The state
	 * of other is undefined until assign() or ~header is called.
	 */
	void	move	(header &other);

	/* return the header's name */
	char const	*name	(void) const { return hr_name; }
	/* return the header's value */
	char const	*value	(void) const { return hr_value; }

private:
	friend struct header_list;

	char	 hr_buffer[HDR_BUFSZ];
	char	*hr_name;	/* name, == hr_buffer or else alloc'd data	*/
	char	*hr_value;	/* value					*/
	size_t	 hr_allocd;	/* size of buffer alloced if any		*/
	static pt_allocator<char>	alloc;
};

/*
 * A list of headers found in a request.
 */
struct header_list {
	/* Construct an empty header list. */
	header_list();
	~header_list();
	header_list &operator= (header_list const &other);

	/*
	 * Add a new (header,value) pair to the list.  name and value must be
	 * nul-terminated.
	 */
	void	 add		(char const *name, char const *value);

	/*
	 * As above, but nul terminated is not required.
	 */
	void	 add		(char const *name, size_t namelen,
				 char const *value, size_t valuelen);

	/*
	 * Append ", app" to the end of the previous header.  app need not
	 * be terminated.
	 */
	void	 append_last	(const char *app, size_t applen);

	/*
	 * Remove the named header from the list.
	 */
	void	 remove		(const char *);

	/*
	 * Return a string (allocated with new char[]) containing the request
	 * headers in a form suitable for use in an HTTP request or response.
	 * The caller is expected to delete the returned value.
	 */
	char	*build		(void);

	/*
	 * Return the length of the string build() would return.
	 */
	int	 length		(void) const {
		return hl_len;
	}

	/*
	 * Find a specific header in the list.  Returns NULL if no such header
	 * exists.
	 */
struct header	*find		(const char *name);

private:
	friend struct header_spigot;
	friend struct header_parser;

	header			*hl_last;
	vector<header *, pt_allocator<header *> >	 hl_hdrs;
	int			 hl_len;
};

struct qvalue {
	float	 val;
const	char	*name;

	bool operator< (qvalue const &rhs) const {
		return val < rhs.val;
	}
};

/*
 * header_spigot: output an HTTP response built from a pre-defined list
 * of headers.  used for responses generated by us.
 */
struct header_spigot : io::spigot
{
	header_spigot(int, char const *);

	void	add		(char const *, char const *);
	void	body		(string const &);
	void	body		(string &);
	void	sp_uncork	(void);
	void	sp_cork		(void);

private:
	header_list	_headers;
	bool		_built;
	string		_first;
	string		_body;
	net::buffer	_buf;
	bool		_corked;
};

/*
 * header_parser: parse and store headers.  This is both a sink (for
 * reading headers and parsing them) and a spigot (for sending headers
 * to a backend or client).
 */
struct header_parser : io::sink, io::spigot
{
	header_parser() 
		: _got_reqtype(false)
		, _built(false)
		, _corked(true)
		, _is_response(false)
		, _content_length(-1)
		, _response(0)
		, _is_msie(false)
		, _http_reqtype(REQTYPE_INVALID)
		, _no_keepalive(false)
		, _force_keepalive(false)
		, _follow_redirect(false)
		, _eof(false)
		{
			_flags.f_chunked = 0;
	}

	io::sink_result	data_ready		(char const *buf, size_t len, ssize_t&);
	io::sink_result	data_empty		(void);

	int		parse_reqtype		(char const *buf, char const *endp);
	int		parse_response		(char const *buf, char const *endp);
	void		completed_callback	(void);
	void		error_callback		(void);
	void		sp_cork			(void);
	void		sp_uncork		(void);
	void 		set_response		(void);
	void		sending_restart		(void);

	header_list	 _headers;
	bool		 _got_reqtype;
	http_version	 _http_vers;
	imstring	 _http_path;
	bool		 _built;
	bool		 _corked;
	bool		 _is_response;
	ssize_t		 _content_length;
	int		 _response;
	bool		 _is_msie;
	int		 _http_reqtype;
	imstring	 _http_host;
	string		 _http_backend;
	bool		 _no_keepalive;
	bool		 _force_keepalive;
	bool		 _follow_redirect;
	imstring	 _location;

	bool		 _eof;
	net::buffer	 _buf;

	struct {
		unsigned int	f_chunked:1;
	}		 _flags;
};

void whttp_header_init(void);

#endif
