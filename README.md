# wp-plugin
Progress:\
__done__: 100% refactored, same behaviour as original\
__quick_fix__: simplified behaviour, just make it runnable\
AC:\
apply_filters() __done__\
add_filter() __done__\
do_action() __done__\
__class__: wp-hook __done__\
related to previous func: _wp_call_all_hook() __done__\
\
absint() __done__\
wp_upload_dir() __quick_fix__\
wp_die() __quick_fix__\
get_current_blog_id() __quick_fix__\
get_the_title() __quick_fix__\
\
esc_html() __done__\
related to previous func: wp_check_invalid_utf8() __done__; wp_kses_normalize_entities() __done__; _wp_specialchars() __done__;\
\
__class__: wp-wpdb __working__

BC:\
is_multisite() __done__\
add_action()  __done__\
register_deactivation_hook() __done__\
register_activation_hook() __done__\
related to above 2 functions: plugin_basename() __done__; wp_normalize_path() __done__; wp_is_stream() __done__;\
\
wp_enqueue_style() __quick_fix__\
plugin_dir_url() __quick_fix__\
related to above func: plugins_url() __quick_fix__; set_url_scheme() __quick_fix__;\
current_user_can() __quick_fix__\
__() __quick_fix__\
add_menu_page() __done__\
related to above func: sanitize_title() __quick_fix__; get_plugin_page_hookname() __done__;\
_e()  __quick_fix__\
wp_count_posts() __quick_fix__\
get_the_ID() __done__\
related to above func: get_post() __quick_fix__;\
wp_verify_nonce() __quick_fix__;\
related to class wp-list-table: wp_parse_args() __done__; wp_parse_str() __done__; convert_to_screen() __quick_fix__; sanitize_key() __done__\
wp_debug_backtrace_summary() __done__\
has_filter() __done__\
\

__class__: wp-wp-list-table __done__\
__class__: wp-list-table __done__\
__class__: wp-query __done__\
\

is_admin() __done__\
esc_url() __done__\
_deep_replace() __done__\
wp_parse_url() __done__\
_get_component_from_parsed_url_array() __done__\
_wp_translate_php_url_constant_to_key() __done__\
wp_allowed_protocols() __done__\
did_action() __done__\
\
\
__Steps to set up mysql table.__\
database name - wptest\
create table wpforms_db (form_id bigint(20) NOT NULL AUTO_INCREMENT, form_post_id bigint(20) NOT NULL, form_value longtext NOT NULL, form_date datetime, PRIMARY KEY (form_id));\
insert into wpforms_db (form_id, form_post_id, form_value, form_date) values (0, 2 ,'test2', '2021-10-05 11:23:44');\
