<?php
/**
 * WP_List_Table for JEM Funnels
 *
 * @package Jazzedge_Marketing
 */

if (!defined('ABSPATH')) {
    exit;
}

class JEM_Funnels_List_Table extends WP_List_Table {

    private $database;

    public function __construct($database) {
        parent::__construct(array(
            'singular' => 'funnel',
            'plural' => 'funnels',
            'ajax' => false,
        ));
        $this->database = $database;
    }

    public function get_columns() {
        return array(
            'id' => __('ID', 'jazzedge-marketing'),
            'name' => __('Name', 'jazzedge-marketing'),
            'shortcode' => __('Shortcode', 'jazzedge-marketing'),
            'optins' => __('Opt-ins', 'jazzedge-marketing'),
            'download_pct' => __('Download %', 'jazzedge-marketing'),
            'purchase_pct' => __('Purchase Click %', 'jazzedge-marketing'),
            'status' => __('Status', 'jazzedge-marketing'),
            'actions' => __('Actions', 'jazzedge-marketing'),
        );
    }

    public function prepare_items() {
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $funnels = $this->database->get_funnels();
        $this->items = array();
        foreach ($funnels as $f) {
            $f = (object) $f;
            $optins = $this->database->count_events($f->id, 'opt_in');
            $downloads = $this->database->count_events($f->id, 'download_click');
            $purchases = $this->database->count_events($f->id, 'purchase_click');
            $download_pct = $optins > 0 ? round($downloads / $optins * 100, 1) : 0;
            $purchase_pct = $optins > 0 ? round($purchases / $optins * 100, 1) : 0;
            $this->items[] = (object) array(
                'id' => $f->id,
                'name' => $f->name,
                'optins' => $optins,
                'download_pct' => $download_pct,
                'purchase_pct' => $purchase_pct,
                'active' => (int) $f->active,
            );
        }
        $this->set_pagination_args(array(
            'total_items' => count($this->items),
            'per_page' => $per_page,
        ));
    }

    public function get_sortable_columns() {
        return array();
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
                return (int) $item->id;
            case 'name':
                return esc_html($item->name);
            case 'shortcode':
                $shortcode = '[jem_marketing id="' . (int) $item->id . '"]';
                return '<div class="jem-shortcode-cell"><input type="text" readonly value="' . esc_attr($shortcode) . '" onclick="this.select()" /><button type="button" class="button button-small jem-copy-btn" data-copy="' . esc_attr($shortcode) . '">' . esc_html__('Copy', 'jazzedge-marketing') . '</button></div>';
            case 'optins':
                return (int) $item->optins;
            case 'download_pct':
                return $item->download_pct . '%';
            case 'purchase_pct':
                return $item->purchase_pct . '%';
            case 'status':
                return $item->active ? '<span class="jem-status-active">' . esc_html__('Active', 'jazzedge-marketing') . '</span>' : '<span class="jem-status-inactive">' . esc_html__('Inactive', 'jazzedge-marketing') . '</span>';
            case 'actions':
                return '<a href="' . esc_url(admin_url('admin.php?page=jem-marketing&action=edit&id=' . $item->id)) . '">' . esc_html__('Edit', 'jazzedge-marketing') . '</a>';
            default:
                return '';
        }
    }
}
