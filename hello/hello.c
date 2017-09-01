/* hello extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_hello.h"

PHP_MINIT_FUNCTION(hello)
{
//    ZEND_INIT_MODULE_GLOBALS(array, php_array_init_globals, NULL);

    REGISTER_LONG_CONSTANT("HELLO_CONST_LONG", 1, CONST_CS | CONST_PERSISTENT);
    REGISTER_STRING_CONSTANT("HELLO_CONST_STRING", "hello_string", CONST_CS | CONST_PERSISTENT);

    return SUCCESS;
}

PHP_MINFO_FUNCTION(hello)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "hello support", "enabled");
	php_info_print_table_end();
}

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
    array_init(return_value);
    // zend_hash_real_init(Z_ARRVAL_P(return_value), 1);

    ZEND_HASH_FOREACH_VAL(&ht, entry) {
                zend_hash_next_index_insert(Z_ARRVAL_P(return_value), entry);
    } ZEND_HASH_FOREACH_END();
}

PHP_FUNCTION(hello_array_merge)
{
    zval *dest, *src, *args, *entry;
    ulong argc;

    array_init(return_value);

    ZEND_PARSE_PARAMETERS_START(2, 2)
            Z_PARAM_ARRAY(dest)
            Z_PARAM_ARRAY(src)
    ZEND_PARSE_PARAMETERS_END();

    // hash合并，类似array + array
    zend_hash_merge(Z_ARRVAL_P(dest), Z_ARRVAL_P(src), zval_add_ref, 1);

    // hash追加，类似array_merge
//    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(src), entry)
//            zend_hash_next_index_insert(Z_ARRVAL_P(dest), entry);
//    ZEND_HASH_FOREACH_END();

    ZVAL_ARR(return_value, Z_ARRVAL_P(dest));
}

PHP_FUNCTION(hello_array_sum)
{
    zval *array, *entry, entry_t;

    ZEND_PARSE_PARAMETERS_START(1, 1)
            Z_PARAM_ARRAY(array)
    ZEND_PARSE_PARAMETERS_END();

    ZVAL_LONG(return_value, 0);

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(array), entry)
            if (Z_TYPE_P(entry) == IS_OBJECT || Z_TYPE_P(entry) == IS_ARRAY) {
                continue;
            }
            ZVAL_COPY(&entry_t, entry);

            convert_scalar_to_number(&entry_t);
            fast_add_function(return_value, return_value, &entry_t);
    ZEND_HASH_FOREACH_END();
}

/* {{{ arginfo
 */
ZEND_BEGIN_ARG_INFO(arginfo_hello_array_init, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_hello_array_merge, 0, 0, 2)
    ZEND_ARG_INFO(0, arg)
    ZEND_ARG_INFO(0, arg)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_hello_array_sum, 0, 0, 1)
                ZEND_ARG_INFO(0, arg)
ZEND_END_ARG_INFO()
/* }}} */

/* {{{ hello_functions[]
 */
const zend_function_entry hello_functions[] = {
	PHP_FE(hello_array_init,		arginfo_hello_array_init)
    PHP_FE(hello_array_merge,		arginfo_hello_array_merge)
    PHP_FE(hello_array_sum,		    arginfo_hello_array_sum)
	PHP_FE_END
};
/* }}} */

/* {{{ hello_module_entry
 */
zend_module_entry hello_module_entry = {
	STANDARD_MODULE_HEADER,
	"hello",					/* Extension name */
	hello_functions,			/* zend_function_entry */
    PHP_MINIT(hello),           /* PHP_MINIT - Module initialization */
	NULL,						/* PHP_MSHUTDOWN - Module shutdown */
	NULL,						/* PHP_RINIT - Request initialization */
	NULL,						/* PHP_RSHUTDOWN - Request shutdown */
	PHP_MINFO(hello),			/* PHP_MINFO - Module info */
	PHP_HELLO_VERSION,		    /* Version */
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_HELLO
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(hello)
#endif
