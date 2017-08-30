/* hello extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_hello.h"

extern void hello_shuffle();

/* {{{ void hello_test1()
 */
PHP_FUNCTION(hello_test1)
{
	ZEND_PARSE_PARAMETERS_NONE();

	php_printf("The extension %s is loaded and working!\r\n", "hello");
}
/* }}} */

/* {{{ string hello_test2( [ string $var ] )
 */
PHP_FUNCTION(hello_test2)
{
	char *var = "World";
	size_t var_len = sizeof("World") - 1;
	zend_string *retval;

	ZEND_PARSE_PARAMETERS_START(0, 1)
		Z_PARAM_OPTIONAL
		Z_PARAM_STRING(var, var_len)
	ZEND_PARSE_PARAMETERS_END();

	retval = strpprintf(0, "Hello %s", var);

	RETURN_STR(retval);
}
/* }}}*/

/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(hello)
{
#if defined(ZTS) && defined(COMPILE_DL_HELLO)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(hello)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "hello support", "enabled");
	php_info_print_table_end();
}
/* }}} */

// hello_array_init(1, 2, 3, 4, 5);
PHP_FUNCTION(hello_array_init)
{
    zval *args, new_var;
    ulong argc;

    HashTable ht;

    ZEND_PARSE_PARAMETERS_START(0, -1)
            Z_PARAM_VARIADIC('+', args, argc)
    ZEND_PARSE_PARAMETERS_END();

    zend_hash_init(&ht, 1, NULL, ZVAL_PTR_DTOR, 0);
    for (int i = 0; i < argc; i++) {
        ZVAL_COPY(&new_var, &args[i]);

        if (zend_hash_next_index_insert(&ht, &new_var) == NULL) {
            if (Z_REFCOUNTED(new_var)) Z_DELREF(new_var);
            php_error_docref(NULL, E_WARNING, "Cannot add element to the array as the next element is already occupied");
            RETURN_FALSE;
        }
    }

    zval *entry;
    array_init_size(return_value, 3);
    zend_hash_real_init(Z_ARRVAL_P(return_value), 1);

    ZEND_HASH_FILL_PACKED(Z_ARRVAL_P(return_value)) {
        ZEND_HASH_FOREACH_VAL(&ht, entry) {
                    if (UNEXPECTED(Z_ISREF_P(entry) && Z_REFCOUNT_P(entry) == 1)) {
                        entry = Z_REFVAL_P(entry);
                    }
                    Z_TRY_ADDREF_P(entry);
                    ZEND_HASH_FILL_ADD(entry);
                } ZEND_HASH_FOREACH_END();
    } ZEND_HASH_FILL_END();

//	RETURN_TRUE;
}

/* {{{ arginfo
 */
ZEND_BEGIN_ARG_INFO(arginfo_hello_test1, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_hello_test2, 0)
	ZEND_ARG_INFO(0, str)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_hello_array_init, 0)
ZEND_END_ARG_INFO()
/* }}} */

/* {{{ hello_functions[]
 */
const zend_function_entry hello_functions[] = {
	PHP_FE(hello_test1,		arginfo_hello_test1)
	PHP_FE(hello_test2,		arginfo_hello_test2)
	PHP_FE(hello_array_init,		arginfo_hello_array_init)
	PHP_FE_END
};
/* }}} */

/* {{{ hello_module_entry
 */
zend_module_entry hello_module_entry = {
	STANDARD_MODULE_HEADER,
	"hello",					/* Extension name */
	hello_functions,			/* zend_function_entry */
	NULL,							/* PHP_MINIT - Module initialization */
	NULL,							/* PHP_MSHUTDOWN - Module shutdown */
	PHP_RINIT(hello),			/* PHP_RINIT - Request initialization */
	NULL,							/* PHP_RSHUTDOWN - Request shutdown */
	PHP_MINFO(hello),			/* PHP_MINFO - Module info */
	PHP_HELLO_VERSION,		/* Version */
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_HELLO
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(hello)
#endif
