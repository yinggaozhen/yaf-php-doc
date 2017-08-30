/* hello extension for PHP */

#ifndef PHP_HELLO_H
# define PHP_HELLO_H

extern zend_module_entry hello_module_entry;
# define phpext_hello_ptr &hello_module_entry

# define PHP_HELLO_VERSION "0.1.0"

# if defined(ZTS) && defined(COMPILE_DL_HELLO)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

#endif	/* PHP_HELLO_H */
