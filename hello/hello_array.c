#include "php.h"

void hello_array_init(zval *array)
{
    zend_long n_nums;
    HashTable *hash;

    n_nums = zend_hash_num_elements(Z_ARRVAL_P(array));

    if (n_nums <= 1) {
        return;
    }

    hash = Z_ARRVAL_P(array);
//	zend_print_flat_zval_r(array);
}