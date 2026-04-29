<?php
/**
 * Plugin Name: JE FC Import Users
 * Description: Creates WordPress user accounts for FluentCart customers who don't have one. No emails sent.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', function () {
    add_management_page(
        'Import FC Customers as Users',
        'Import FC Users',
        'manage_options',
        'je-fc-import-users',
        'je_fc_import_users_page'
    );
} );

function je_fc_import_users_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $results = null;

    if ( isset( $_POST['je_fc_import_nonce'] ) && wp_verify_nonce( $_POST['je_fc_import_nonce'], 'je_fc_import_users' ) ) {
        $results = je_fc_run_import();
    }

    $preview = je_fc_get_preview();
    ?>
    <div class="wrap">
        <h1>Import FluentCart Customers as WordPress Users</h1>
        <p>Creates a WordPress account for each FluentCart customer who doesn't already have one.
           <strong>No emails are sent.</strong> Customers can use &ldquo;Forgot Password&rdquo; to set their password.</p>

        <?php if ( $results !== null ) : ?>
            <div class="notice notice-success"><p>Import complete.</p></div>
            <h2>Results</h2>
            <table class="widefat striped" style="max-width:700px">
                <thead><tr><th>Customer</th><th>Email</th><th>Result</th></tr></thead>
                <tbody>
                <?php foreach ( $results as $r ) : ?>
                    <tr>
                        <td><?php echo esc_html( $r['name'] ); ?></td>
                        <td><?php echo esc_html( $r['email'] ); ?></td>
                        <td><?php echo esc_html( $r['status'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <h2>Customers Without a WordPress Account (<?php echo count( $preview ); ?>)</h2>
            <?php if ( empty( $preview ) ) : ?>
                <p>All FluentCart customers already have a WordPress account.</p>
            <?php else : ?>
                <table class="widefat striped" style="max-width:700px">
                    <thead><tr><th>#</th><th>Name</th><th>Email</th></tr></thead>
                    <tbody>
                    <?php foreach ( $preview as $c ) : ?>
                        <tr>
                            <td><?php echo (int) $c['id']; ?></td>
                            <td><?php echo esc_html( trim( $c['first_name'] . ' ' . $c['last_name'] ) ); ?></td>
                            <td><?php echo esc_html( $c['email'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" style="margin-top:16px">
                    <?php wp_nonce_field( 'je_fc_import_users', 'je_fc_import_nonce' ); ?>
                    <p><input type="submit" class="button button-primary" value="Create <?php echo count( $preview ); ?> WordPress User(s)"></p>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

function je_fc_get_customers() {
    global $wpdb;
    $table = $wpdb->prefix . 'fct_customers';
    return $wpdb->get_results(
        "SELECT id, first_name, last_name, email FROM {$table} WHERE email != '' ORDER BY id ASC",
        ARRAY_A
    ) ?: [];
}

function je_fc_get_preview() {
    $out = [];
    foreach ( je_fc_get_customers() as $c ) {
        if ( ! email_exists( $c['email'] ) ) {
            $out[] = $c;
        }
    }
    return $out;
}

function je_fc_run_import() {
    // Suppress all new-user notification emails for this request.
    add_filter( 'wp_new_user_notification_email',       '__return_false' );
    add_filter( 'wp_new_user_notification_email_admin', '__return_false' );

    $results = [];
    foreach ( je_fc_get_customers() as $c ) {
        $email = sanitize_email( $c['email'] );
        if ( email_exists( $email ) ) {
            $results[] = [
                'name'   => trim( $c['first_name'] . ' ' . $c['last_name'] ),
                'email'  => $email,
                'status' => 'Already exists — skipped',
            ];
            continue;
        }

        $first    = sanitize_text_field( $c['first_name'] );
        $last     = sanitize_text_field( $c['last_name'] );
        $username = je_fc_unique_username( $email, $first, $last );

        $user_id = wp_insert_user( [
            'user_login'   => $username,
            'user_email'   => $email,
            'first_name'   => $first,
            'last_name'    => $last,
            'display_name' => trim( $first . ' ' . $last ) ?: $username,
            'user_pass'    => wp_generate_password( 24 ),
            'role'         => 'subscriber',
        ] );

        if ( is_wp_error( $user_id ) ) {
            $results[] = [
                'name'   => trim( $c['first_name'] . ' ' . $c['last_name'] ),
                'email'  => $email,
                'status' => 'Error: ' . $user_id->get_error_message(),
            ];
        } else {
            $results[] = [
                'name'   => trim( $c['first_name'] . ' ' . $c['last_name'] ),
                'email'  => $email,
                'status' => 'Created (ID ' . $user_id . ')',
            ];
        }
    }

    return $results;
}

function je_fc_unique_username( $email, $first, $last ) {
    // Prefer first+last, fall back to email prefix.
    $base = '';
    if ( $first && $last ) {
        $base = strtolower( $first . '.' . $last );
    } elseif ( $first ) {
        $base = strtolower( $first );
    } else {
        $base = strstr( $email, '@', true );
    }

    $base     = sanitize_user( $base, true );
    $username = $base;
    $i        = 2;
    while ( username_exists( $username ) ) {
        $username = $base . $i;
        $i++;
    }

    return $username;
}
