-- Based more or less on the public interwiki map from MeatballWiki
-- Default interwiki prefixes...

INSERT INTO interwiki (iw_prefix,iw_url,iw_local) VALUES
('AbbeNormal','http://www.ourpla.net/cgi-bin/pikie.cgi?$1',0),
('AcadWiki','http://xarch.tu-graz.ac.at/autocad/wiki/$1',0),
('Acronym','http://www.acronymfinder.com/af-query.asp?String=exact&Acronym=$1',0),
('Advogato','http://www.advogato.org/$1',0),
('AIWiki','http://www.ifi.unizh.ch/ailab/aiwiki/aiw.cgi?$1',0),
('ALife','http://news.alife.org/wiki/index.php?$1',0),
('AndStuff','http://andstuff.org/wiki.php?$1',0),
('Annotation','http://bayle.stanford.edu/crit/nph-med.cgi/$1',0),
('AnnotationWiki','http://www.seedwiki.com/page.cfm?wikiid=368&doc=$1',0),
('AwarenessWiki','http://taoriver.net/aware/$1',0),
('BenefitsWiki','http://www.benefitslink.com/cgi-bin/wiki.cgi?$1',0),
('BridgesWiki','http://c2.com/w2/bridges/$1',0),
('C2find','http://c2.com/cgi/wiki?FindPage&value=$1',0),
('Cache','http://www.google.com/search?q=cache:$1',0),
('CLiki','http://ww.telent.net/cliki/$1',0),
('CmWiki','http://www.ourpla.net/cgi-bin/wiki.pl?$1',0),
('CreationMatters','http://www.ourpla.net/cgi-bin/wiki.pl?$1',0),
('DejaNews','http://www.deja.com/=dnc/getdoc.xp?AN=$1',0),
('Dictionary','http://www.dict.org/bin/Dict?Database=*&Form=Dict1&Strategy=*&Query=$1',0),
('DiveIntoOsx','http://diveintoosx.org/$1',0),
('DocBook','http://docbook.org/wiki/moin.cgi/$1',0),
('DolphinWiki','http://www.object-arts.com/wiki/html/Dolphin/$1',0),
('EfnetCeeWiki','http://purl.net/wiki/c/$1',0),
('EfnetCppWiki','http://purl.net/wiki/cpp/$1',0),
('EfnetPythonWiki','http://purl.net/wiki/python/$1',0),
('EfnetXmlWiki','http://purl.net/wiki/xml/$1',0),
('EljWiki','http://elj.sourceforge.net/phpwiki/index.php/$1',0),
('EmacsWiki','http://www.emacswiki.org/cgi-bin/wiki.pl?$1',0),
('FinalEmpire','http://final-empire.sourceforge.net/cgi-bin/wiki.pl?$1',0),
('Foldoc','http://www.foldoc.org/foldoc/foldoc.cgi?$1',0),
('FoxWiki','http://fox.wikis.com/wc.dll?Wiki~$1',0),
('FreeBSDman','http://www.FreeBSD.org/cgi/man.cgi?apropos=1&query=$1',0),
('Google','http://www.google.com/search?q=$1',0),
('GoogleGroups','http://groups.google.com/groups?q=$1',0),
('GreenCheese','http://www.greencheese.org/$1',0),
('HammondWiki','http://www.dairiki.org/HammondWiki/index.php3?$1',0),
('Haribeau','http://wiki.haribeau.de/cgi-bin/wiki.pl?$1',0),
('IAWiki','http://www.IAwiki.net/$1',0),
('IMDB','http://us.imdb.com/Title?$1',0),
('JargonFile','http://sunir.org/apps/meta.pl?wiki=JargonFile&redirect=$1',0),
('JiniWiki','http://www.cdegroot.com/cgi-bin/jini?$1',0),
('JspWiki','http://www.ecyrd.com/JSPWiki/Wiki.jsp?page=$1',0),
('KmWiki','http://www.voght.com/cgi-bin/pywiki?$1',0),
('KnowHow','http://www2.iro.umontreal.ca/~paquetse/cgi-bin/wiki.cgi?$1',0),
('LanifexWiki','http://opt.lanifex.com/cgi-bin/wiki.pl?$1',0),
('LegoWiki','http://www.object-arts.com/wiki/html/Lego-Robotics/$1',0),
('LinuxWiki','http://www.linuxwiki.de/$1',0),
('LugKR','http://lug-kr.sourceforge.net/cgi-bin/lugwiki.pl?$1',0),
('MathSongsWiki','http://SeedWiki.com/page.cfm?wikiid=237&doc=$1',0),
('MbTest','http://www.usemod.com/cgi-bin/mbtest.pl?$1',0),
('MeatBall','http://www.usemod.com/cgi-bin/mb.pl?$1',0),
('MetaWiki','http://sunir.org/apps/meta.pl?$1',0),
('MetaWikiPedia','http://meta.wikipedia.org/wiki/$1',0),
('MoinMoin','http://purl.net/wiki/moin/$1',0),
('MuWeb','http://www.dunstable.com/scripts/MuWebWeb?$1',0),
('NetVillage','http://www.netbros.com/?$1',0),
('OpenWiki','http://openwiki.com/?$1',0),
('OrgPatterns','http://www.bell-labs.com/cgi-user/OrgPatterns/OrgPatterns?$1',0),
('PangalacticOrg','http://www.pangalactic.org/Wiki/$1',0),
('PersonalTelco','http://www.personaltelco.net/index.cgi/$1',0),
('PhpWiki','http://phpwiki.sourceforge.net/phpwiki/index.php?$1',0),
('Pikie','http://pikie.darktech.org/cgi/pikie?$1',0),
('PPR','http://c2.com/cgi/wiki?$1',0),
('PurlNet','http://purl.oclc.org/NET/$1',0),
('PythonInfo','http://www.python.org/cgi-bin/moinmoin/$1',0),
('PythonWiki','http://www.pythonwiki.de/$1',0),
('PyWiki','http://www.voght.com/cgi-bin/pywiki?$1',0),
('SeaPig','http://www.seapig.org/ $1',0),
('SeattleWireless','http://seattlewireless.net/?$1',0),
('SenseisLibrary','http://senseis.xmp.net/?$1',0),
('Shakti','http://cgi.algonet.se/htbin/cgiwrap/pgd/ShaktiWiki/$1',0),
('SourceForge','http://sourceforge.net/$1',0),
('Squeak','http://minnow.cc.gatech.edu/squeak/$1',0),
('StrikiWiki','http://ch.twi.tudelft.nl/~mostert/striki/teststriki.pl?$1',0),
('SVGWiki','http://www.protocol7.com/svg-wiki/default.asp?$1',0),
('Tavi','http://tavi.sourceforge.net/index.php?$1',0),
('TmNet','http://www.technomanifestos.net/?$1',0),
('TMwiki','http://www.EasyTopicMaps.com/?page=$1',0),
('TWiki','http://twiki.org/cgi-bin/view/$1',0),
('TwistedWiki','http://purl.net/wiki/twisted/$1',0),
('Unreal','http://wiki.beyondunreal.com/wiki/$1',0),
('UseMod','http://www.usemod.com/cgi-bin/wiki.pl?$1',0),
('VisualWorks','http://wiki.cs.uiuc.edu/VisualWorks/$1',0),
('WebDevWikiNL','http://www.promo-it.nl/WebDevWiki/index.php?page=$1',0),
('WebSeitzWiki','http://webseitz.fluxent.com/wiki/$1',0),
('Why','http://clublet.com/c/c/why?$1',0),
('Wiki','http://c2.com/cgi/wiki?$1',0),
('WikiPedia','http://www.wikipedia.org/wiki/$1',0),
('Wiktionary','http://wiktionary.org/wiki/$1',0),
('WikiWorld','http://WikiWorld.com/wiki/index.php/$1',0),
('YpsiEyeball','http://sknkwrks.dyndns.org:1957/writewiki/wiki.pl?$1',0),
('ZWiki','http://www.zwiki.org/$1',0),
('ReVo','http://purl.org/NET/voko/revo/art/$1.html',0),
('EcheI','http://www.ikso.net/cgi-bin/wiki.pl?$1',0),
('EcxeI','http://www.ikso.net/cgi-bin/wiki.pl?$1',0),
('EĉeI','http://www.ikso.net/cgi-bin/wiki.pl?$1',0),
('JEFO','http://www.esperanto-jeunes.org/vikio/index.php?$1',0),
('PMEG','http://www.bertilow.com/pmeg/$1.php',0),
('TEJO','http://www.tejo.org/vikio/$1',0),
('USEJ','http://www.tejo.org/usej/$1',0),
('UEA','http://www.tejo.org/uea/$1',0),
('Turismo','http://www.tejo.org/turismo/$1',0),
('GEJ','http://www.esperanto.de/cgi-bin/aktivikio/wiki.pl?$1',0),
('BEMI','http://bemi.free.fr/vikio/index.php?$1',0),
('EnciclopediaLibre','http://enciclopedia.us.es/wiki.phtml?title=$1',0),
('WikiBooks','http://wikibooks.org/wiki/$1',0);

