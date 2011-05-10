VER=2.2.2
SUBDIR=openid-php-openid-782224d
# how to make that one predictable easily?

php-openid: Auth

Auth:	php-openid-$(VER).tar.gz
	tar -xzf php-openid-$(VER).tar.gz $(SUBDIR)/Auth
	mv $(SUBDIR)/Auth ./
	rmdir $(SUBDIR)

php-openid-$(VER).tar.gz:
	wget --no-check-certificate https://github.com/openid/php-openid/tarball/$(VER) -O php-openid-$(VER).tar.gz

clean:
	rm -rf Auth php-openid-$(VER).tar.bz2
