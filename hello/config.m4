PHP_ARG_ENABLE(hello, whether to enable hello support,
[  --enable-hello          Enable hello support], no)

if test "$PHP_HELLO" != "no"; then
  AC_DEFINE(HAVE_HELLO, 1, [ Have hello support ])
  PHP_NEW_EXTENSION(hello, hello.c, $ext_shared)
fi
