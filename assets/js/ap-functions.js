/**
 * Contain general JavaScript functions used in AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 * @since 2.0
 */

/**
 * For returning default value if passed value is undefined.
 * @param  {mixed} $value   A value to check
 * @param  {mixed} $default return this if $value is undefined
 * @return {string}
 * @since 2.0
 **/
function ap_default($value, $default){
	if(typeof $value !== 'undefined')
		return $value;

	return $default;
}