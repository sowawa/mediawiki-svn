/* @(#) $Header$ */
/* This source code is in the public domain. */
/*
 * trickle: copy one directory to another, slowly.
 */

#ifndef TRICKLE_H
#define TRICKLE_H

#include <sys/types.h>

#include <stdio.h>

#define min(x,y) ((x) < (y) ? (x) : (y))
#define max(x,y) ((x) < (y) ? (y) : (x))

extern int pflag;
extern char *dest;		/* destination name	*/
extern const char *progname;	/* argv[0]		*/
extern char *curdir;		/* cwd name		*/

size_t write_blocked(void *buf, size_t size, FILE *file);

/** Tar support */

/*
 * POSIX 1003.1-1990/SUSv2 tar(1) header.
 *
 * Regarding name/prefix, SUSv2 says:
 *
 *    The name and the prefix fields produce the pathname of the file. The 
 *    hierarchical relationship of the file can be retained by specifying the 
 *    pathname as a path prefix, and a slash character and filename as the 
 *    suffix. A new pathname is formed, if prefix is not an empty string (its 
 *    first character is not NUL), by concatenating prefix (up to the first NUL 
 *    character), a slash character and name; otherwise, name is used alone. 
 *    In either case, name is terminated at the first NUL character. If prefix 
 *    begins with a NUL character, it will be ignored. In this manner, pathnames 
 *    of at most 256 characters can be supported.
 */
struct tar {
	char tr_name[100];	/* file name		*/
	char tr_mode[8];	/* mode			*/
	char tr_uid[8];		/* owner (numeric)	*/
	char tr_gid[8];		/* group (numeric)	*/
	char tr_size[12];	/* size in bytes	*/
	char tr_mtime[12];	/* mtime		*/
	char tr_chksum[8];	/* checksum of header	*/
	char tr_typeflag;	/* file type		*/
	char tr_linkname[100];	/* symlink target	*/
	char tr_magic[6];	/* tar magic: "ustar "	*/
	char tr_version[2];	/* tar version: "00"	*/
	char tr_uname[32];	/* owner (string)	*/
	char tr_gname[32];	/* group (string)	*/
	char tr_devmajor[8];	/* device major		*/
	char tr_devminor[8];	/* device minor		*/
	char tr_prefix[155];	/* directory		*/
};

void tar_writeheader(FILE *file, const char *name);
void tar_writeeof(FILE *file);

#endif
