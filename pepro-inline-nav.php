<?php
/*
Plugin Name: PeproDev Inline Navigation
Description: Add a floating navigation to your posts and pages to improve Readability and UX, works with WPBakery Page Builder only
Contributors: amirhosseinhpv,peprodev
Tags: functionality, visual composer, navigation, readability, UX
Author: Pepro Dev. Group
Developer: Amirhosseinhpv
Author URI: https://pepro.dev/
Developer URI: https://hpv.im/
Plugin URI: https://pepro.dev/inline-navigation
Version: 1.0.1
Stable tag: 1.0.1
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 5.6
Text Domain: pepinav
Domain Path: /languages
Copyright: (c) 2020 Pepro Dev. Group, All rights reserved.
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
defined("ABSPATH") or die("Pepro Inline Navigation :: Unauthorized Access!");

if (!class_exists("PeproPostInlineNav")){
  class PeproPostInlineNav
  {
      private static $_instance = null;
      private $td;
      private $plugin_dir;
      private $plugin_url;
      private $assets_url;
      private $plugin_basename;
      private $plugin_file;
      private $version;
      private $db_slug;
      private $title;
      private $title_w;
      private $db_table = null;
      private $manage_links = array();
      private $meta_links = array();
      public function __construct()
      {
        global $wpdb;
        $this->td = "pepinav";
        self::$_instance = $this;
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugins_url("", __FILE__);
        $this->assets_url = plugins_url("/assets", __FILE__);
        $this->plugin_basename = plugin_basename(__FILE__);
        $this->plugin_file = __FILE__;
        $this->version = "1.0.1";
        $this->db_slug = $this->td;
        $this->db_table = $wpdb->prefix . $this->db_slug;
        $this->title = __("Inline Navigation", $this->td);
        $this->title_l = __("Pepro Inline Navigation", $this->td);
        $this->title_s = __("Inline Navigation", $this->td);
        $this->title_w = sprintf(__("%2\$s ver. %1\$s", $this->td), $this->version, $this->title);
        add_action("init", array($this, 'init_plugin'));
      }
      /**
       * Init Plugin
       *
       * @method init_plugin
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function init_plugin()
      {
        add_filter("plugin_action_links_{$this->plugin_basename}", array($this, 'plugins_row_links'));
        add_action("plugin_row_meta", array( $this, 'plugin_row_meta' ), 10, 2);
        add_shortcode("post-navs", array($this, 'inline_nav_shortcode_handler') );
        if ($this->_vc_activated()){
            add_action( 'vc_before_init', array($this,'integrate_With_VC'));
        }
        else{
          add_action('admin_notices',function () {
          echo "<div class=\"notice error\"><p>".
            sprintf(
              _x('%1$s needs %2$s to be installed and activated to function correctly.',"required-plugin","$this->td"),
              "<strong>{$this->title_l}</strong>",
              "<strong>"._x("WPBakery Page Builder","required-plugin","$this->td")."</strong>"
            ).
          "</p></div>";
          });
        }
      }
      /**
       * add WPBakery Page Builder widget
       *
       * @method integrate_With_VC
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function integrate_With_VC()
      {
        vc_map(
            array(
                'name' => __("Inline Navigation",$this->td),
                'description' => __( 'Adds inline navigation items', "$this->td" ),
                'base' => "post-navs",
                'class' => "{$this->td}__class",
                'icon' => "{$this->assets_url}/img/peprodev.svg",
                'show_settings_on_create' => true,
                'admin_enqueue_css' => "{$this->assets_url}/css/vc.init.css",
                'category' => __('Pepro Elements', "$this->td"),
                'params' => array(
                  array(
                      'type' => 'param_group',
                      'value' => '',
                      'param_name' => 'group_name',
                      'params' => array(
                          array(
                            'edit_field_class' => 'vc_column vc_col-sm-6',
                              'type' => 'textfield',
                              'admin_label' => true,
                              'heading' => __('Nav. Title', "$this->td"),
                              'description' => __('Title will be shown as name of navigation item.', "$this->td"),
                              'param_name' => 'title',
                          ),
                          array(
                            'edit_field_class' => 'vc_column vc_col-sm-6',
                              'type' => 'textfield',
                              'admin_label' => true,
                              'heading' => __('CSS Selector', "$this->td"),
                              'description' => __('Navigation CSS selector, Use # for ID or . for class selection.', "$this->td"),
                              'param_name' => 'anchor',
                          ),
                      )
                  ),
                  array(
                    'group' => __("Design options", "$this->td" ),
                    'type' => "textfield",
                    'class' => "",
                    'heading' => __("Scroll Offset", "$this->td" ),
                    'edit_field_class' => 'vc_column vc_col-sm-6',
                    'description' => __("e.g. 100", "$this->td" ),
                    'holder' => "div",
                    'admin_label' => false,
                    'param_name' => "threshold",
                  ),
                  array(
                    'group' => __("Design options", "$this->td" ),
                    'type' => "textfield",
                    'class' => "",
                    'heading' => __("Navigation Offset", "$this->td" ),
                    'description' => __("e.g. 5rem", "$this->td" ),
                    'edit_field_class' => 'vc_column vc_col-sm-6',
                    'holder' => "div",
                    'admin_label' => false,
                    'param_name' => "navoffset",
                  ),
                  array(
                    'group' => __("Design options", "$this->td" ),
                    'type' => "textfield",
                    'class' => "",
                    'heading' => __("Element's ID", "$this->td" ),
                    'edit_field_class' => 'vc_column vc_col-sm-6',
                    'holder' => "div",
                    'admin_label' => false,
                    'param_name' => "el_id",
                  ),
                  array(
                    'group' => __("Design options", "$this->td" ),
                    'type' => "textfield",
                    'class' => "",
                    'heading' => __("Element's Class", "$this->td" ),
                    'edit_field_class' => 'vc_column vc_col-sm-6',
                    'holder' => "div",
                    'admin_label' => false,
                    'param_name' => "el_class",
                  ),
                  )
            ));
      }
      /**
       * Get Plugin Meta Links
       *
       * @method get_meta_links
       * @return array meta links
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function get_meta_links()
      {
          if (!empty($this->meta_links)) {return $this->meta_links;}
          $this->meta_links = array(
                  'support'      => array(
                      'title'       => __("Support", "$this->td"),
                      'description' => __("Support", "$this->td"),
                      'icon'        => 'dashicons-admin-site',
                      'target'      => '_self',
                      'url'         => "mailto:support@pepro.dev?subject=$this->title",
                  ),
              );
          return $this->meta_links;
      }
      /**
       * Get Manage Plugins Links
       *
       * @method get_manage_links
       * @return array manage links
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function get_manage_links()
      {
          if (!empty($this->manage_links)){return $this->manage_links;}
          return array();
      }
      /**
       * Inline Nav Shortcode Handler
       *
       * @method inline_nav_shortcode_handler
       * @param array $attr shortcode attributes
       * @return string html data
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function inline_nav_shortcode_handler($attr=array(),$content)
      {
        extract(
          shortcode_atts(
            array(
              "navoffset"   => "4rem",
              "group_name"  => "",
              "threshold"   => "",
              "el_class"    => "",
              "el_id"       => "",
            ),
          $attr)
        );

        if (empty(trim($group_name))){return;}

        $group_data = vc_param_group_parse_atts( $group_name );
        $uniqid = uniqid("nav");
        $data_array = array();
        foreach ($group_data as $key => $value) {
          global $heading_number;
          $heading_number += 1;
          $uniqid = $this->string_camelCase("{$value["title"]}");
          $uniqid = "nav-$heading_number";
          $data_array[$heading_number] = array(
            "title" => $value["title"],
            "anchor" => $value["anchor"],
            "uniqid" => $uniqid,
          );
        }

        wp_enqueue_style( "pepro-inline-navigation", "$this->assets_url/css/front-end.css", array(), current_time( "timestamp" ) , "all");
        wp_enqueue_script( "pepro-inline-navigation", "$this->assets_url/js/front-end.js", array("jquery"), current_time( "timestamp" ) , true);
        wp_localize_script( "pepro-inline-navigation", "peproInlineNavigation", array("offset" => $threshold,"data" => $data_array,) );
        return "<ul class='pepro-inline-navigation-container $el_class' id='$el_id' style='top: $navoffset;'></ul>";
      }
      /**
       * make vaiable name from string
       *
       * @method string_camelCase
       * @param string $input
       * @return string filtered string
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function string_camelCase($input)
      {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace("/[^a-zA-Z0-9]+/", "", $input);
        $str = trim($str);
        $str = lcfirst($str);
        // return "ppc_" . $str . random_int(1, 500);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
      }
      /**
       * check if visual composer is active
       *
       * @method _vc_activated
       * @return boolean true on active and false otherwise
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function _vc_activated()
      {
        if (!is_plugin_active('js_composer/js_composer.php') || !defined('WPB_VC_VERSION')){
          return false;
        }else{
          return true;
        }
      }
      /**
       * hook into plugin's row
       *
       * @method plugins_row_links
       * @param array $links
       * @return array row links
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function plugins_row_links($links)
      {
          foreach ($this->get_manage_links() as $title => $href) {
              array_push($links, "<a href='$href' target='_blank'>$title</a>");
          }
          return $links;
      }
      /**
       * hook into plugin_row_meta
       *
       * @method plugin_row_meta
       * @param array $links meta links
       * @param string $file current plugin file
       * @return array hooked meta links
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public function plugin_row_meta($links, $file)
      {
          if ($this->plugin_basename === $file) {
              $icon_attr = array(
                  'style' => array(
                      'font-size: inherit;',
                      'line-height: inherit;',
                      'display: inline;',
                      'vertical-align: text-top;',
                  ),
              );
              foreach ($this->get_meta_links() as $id => $link) {
                  $title = (!empty($link['icon'])) ? self::do_icon($link['icon'], $icon_attr) . ' ' . esc_html($link['title']) : esc_html($link['title']);
                  $links[ $id ] = '<a href="' . esc_url($link['url']) . '" title="'.esc_attr($link['description']).'" target="'.(empty($link['target'])?"_blank":$link['target']).'">' . $title . '</a>';
              }
          }
          return $links;
      }
      /**
       * print dashicon on plugin row action
       *
       * @method do_icon
       * @param string $icon dashicon code
       * @param array $attr html attrs
       * @return string html icon dom
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public static function do_icon($icon, $attr = array(), $content = '')
      {
          $class = '';
          if (false === strpos($icon, '/') && 0 !== strpos($icon, 'data:') && 0 !== strpos($icon, 'http')) {
              // It's an icon class.
              $class .= ' dashicons ' . $icon;
          } else {
              // It's a Base64 encoded string or file URL.
              $class .= ' vaa-icon-image';
              $attr   = self::merge_attr($attr, array(
                  'style' => array( 'background-image: url("' . $icon . '") !important' ),
              ));
          }

          if (! empty($attr['class'])) {
              $class .= ' ' . (string) $attr['class'];
          }
          $attr['class']       = $class;
          $attr['aria-hidden'] = 'true';

          $attr = self::parse_to_html_attr($attr);
          return '<span ' . $attr . '>' . $content . '</span>';
      }
      /**
       * html attributes array to attrs html
       *
       * @method parse_to_html_attr
       * @param array $array
       * @return string attributes
       * @version 1.0.0
       * @since 1.0.0
       * @license https://pepro.dev/license Pepro.dev License
       */
      public static function parse_to_html_attr($array)
      {
          $str = '';
          if (is_array($array) && ! empty($array)) {
              foreach ($array as $attr => $value) {
                  if (is_array($value)) {
                      $value = implode(' ', $value);
                  }
                  $array[ $attr ] = esc_attr($attr) . '="' . esc_attr($value) . '"';
              }
              $str = implode(' ', $array);
          }
          return $str;
      }

  }

  add_action( 'plugins_loaded', function(){
    global $PeproPostInlineNav;
    load_plugin_textdomain("pepinav", false, dirname(plugin_basename(__FILE__))."/languages/");
    $PeproPostInlineNav = new PeproPostInlineNav;
  });

}

/*################################################################################
END OF PLUGIN || Programming is art // Artist : Amirhosseinhpv [https://hpv.im/]
################################################################################*/
