<?php

/**
 * Proprietary Software - All Rights Reserved
 *
 * This file is part of the Memberium plugin, which is proprietary software developed by Web Power and Light.
 * Unauthorized copying, distribution, or modification of this file, via any medium, is strictly prohibited.
 *
 * Copyright (c) 2012-2025 David J Bullock
 * Web Power and Light
 *
 * For licensing information, please contact Web Power and Light.
 */


 class_exists('m4is_r83' )||die();
 ?><# checked = ( data.value === '1' || data.value === 1 ) ? ' checked="checked"' : ''; #>
<div class="bb-toggle-group">
    <label class="bb-toggle-switch">
        <input type="checkbox" name="{{data.name}}" class="bb-switch-input" value="1" tabindex="1"{{checked}}>
        <span class="bb-switch-label" data-on="On" data-off="Off"></span>
        <span class="bb-switch-handle"></span>
    </label>
</div>

