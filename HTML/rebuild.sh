#!/bin/sh
#
# rebuild.sh: rebuild hypertext with the previous context.
#
# Usage:
#	% sh rebuild.sh
#
cd /tmp/SqliteArray/src && GTAGSCONF=':skip=GPATH,GTAGS,GRTAGS,GSYMS,HTML/,HTML.pub/,html/,tags,TAGS,ID,y.tab.c,y.tab.h,cscope.out,cscope.po.out,cscope.in.out,SCCS/,RCS/,CVS/,CVSROOT/,{arch}/,autom4te.cache/:langmap=c\:.c.h,yacc\:.y,asm\:.s.S,java\:.java,cpp\:.c++.cc.cpp.cxx.hxx.hpp.C.H,php\:.php.php3.phtml:body_begin=<body text='"'"'#191970'"'"' bgcolor='"'"'#f5f5dc'"'"' vlink='"'"'gray'"'"'>:body_end=</body>:table_begin=<table>:table_end=</table>:title_begin=<h1><font color='"'"'#cc0000'"'"'>:title_end=</font></h1>:comment_begin=<i><font color='"'"'green'"'"'>:comment_end=</font></i>:sharp_begin=<font color='"'"'darkred'"'"'>:sharp_end=</font>:brace_begin=<font color='"'"'red'"'"'>:brace_end=</font>:warned_line_begin=<span style='"'"'background-color\:yellow'"'"'>:warned_line_end=</span>:reserved_begin=<b>:reserved_end=</b>:script_alias=/cgi-bin/:ncol#4:tabs#8:normal_suffix=html:gzipped_suffix=ghtml:definition_header=no:suffixes=c,h,y,s,S,java,c++,cc,cpp,cxx,hxx,hpp,C,H,php,php3,phtml:' htags -g -s -a -n -v -w -t SqliteArray /tmp/SqliteArray/html
