<?php

/**
 * Umbrella Accounts for Memberium
 * Copyright (c) 2024 David J Bullock / Web Power and Light
 */


  
function memb_umbrella_get_version(): string {
return m4is_u7102::m4is_c26()->m4is_w45();
 
} 
function memb_umbrella_get_settings(string $key =''){
return m4is_u7102::m4is_c26()->m4is_d326($key );
 
} 
function memb_get_max_children(int $user_id =0 ): int {
$user_id =$user_id ?: m4is_r83::m4is_c26()->m4is_x66();
 return m4is_q82::m4is_d6758($user_id, 'umbrella', 'max_children', 0 );
 
} 
function memb_get_is_parent(int $user_id =0 ): bool {
$user_id =$user_id ?: m4is_r83::m4is_c26()->m4is_x66();
 return m4is_q82::m4is_d6758($user_id, 'umbrella', 'is_parent ', 0 );
 
}

