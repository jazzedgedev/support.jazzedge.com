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
            'invite_code' => __('Invite Code', 'jazzedge-marketing'),
            'email_link' => __('Email Link', 'jazzedge-marketing'),
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
                'invite_code' => isset($f->invite_code) ? $f->invite_code : '',
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
            case 'invite_code':
                $code = isset($item->invite_code) ? $item->invite_code : '';
                $btn = '<button type="button" class="button button-small jem-regenerate-invite-code" data-funnel-id="' . (int) $item->id . '" title="' . esc_attr__('Regenerating will break any existing links using this code.', 'jazzedge-marketing') . '">' . esc_html__('Regenerate', 'jazzedge-marketing') . '</button>';
                return '<code style="font-family:monospace;font-size:11px;">' . esc_html($code) . '</code> ' . $btn;
            case 'email_link':
                $optin_page_id  = get_option( 'jem_optin_page_id', 0 );
                $optin_base_url = $optin_page_id ? get_permalink( $optin_page_id ) : '';
                $invite_code    = isset( $item->invite_code ) ? $item->invite_code : '';
                if ( $optin_page_id && $optin_base_url && ! empty( $invite_code ) ) {
                    $email_link = add_query_arg( 'invite_code', $invite_code, $optin_base_url );
                    return '<button type="button" class="button button-small jem-copy-btn jem-copy-email-btn" data-copy="' . esc_attr( $email_link ) . '" data-type="email-link">' . esc_html__( 'Copy Email Link', 'jazzedge-marketing' ) . '</button>';
                }
                return '<span style="color:#aaa;font-size:12px;">' . esc_html__( 'Set Opt-in Page in Settings first', 'jazzedge-marketing' ) . '</span>';
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
