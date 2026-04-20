<?php
 class_exists('m4is_r83')||die();
 ?><fieldset data-asset-id="<?= $m4is_c864;
 ?>" data-asset-type="<?= $m4is_v5730;
 ?>" data-user-status="<?= $m4is_f3967;
 ?>" class="memb-asset-access-fieldset">

    <legend class="memb-asset-access-legend">Memberium</legend>

    <?php foreach ($m4is_a89 as $m4is_q523){
$m4is_k52736 =$m4is_q523['name'];
 $m4is_j0361 =$m4is_q523['type'];
 $m4is_m661 =$m4is_q523['id'];
 $m4is_s36520 =$m4is_q523['field_name'];
 $m4is_v586 =$m4is_q523['value'];
 $m4is_k617 =!empty($m4is_q523['info'])? $m4is_q523['info']: false;
  echo "<div class=\"memb-asset-access-field-control\" data-setting=\"{$m4is_k52736
}\">";
 $m4is_w42 =$m4is_q523['label'];
 if($m4is_k617 ){
$m4is_w42 .= "<div class=\"memb-asset-access-tooltip\"><span class=\"dashicons dashicons-info\"></span>";
 $m4is_w42 .= "<span class=\"memb-asset-access-tooltiptext\">{$m4is_k617
}</span></div>";
 
}echo sprintf('<label for="%s" class="memb-asset-access-field-label">%s</label>', $m4is_m661, $m4is_w42);
 if($m4is_j0361 === 'text' ){
echo sprintf('<input type="%s" name="%s" id="%s" value="%s" class="widefat %s-field-input"/>', $m4is_j0361, $m4is_s36520, $m4is_m661, esc_attr($m4is_v586), $m4is_j67631 );
 
}elseif($m4is_j0361 === 'select2'){
$m4is_l91805 =isset($m4is_q523['data'])? $m4is_q523['data']: 0;
 $m4is_k46028 =isset($m4is_q523['multiple'])? (int)$m4is_q523['multiple']: 0;
 $m4is_c6792 =$m4is_k46028 > 0 ? " data-multiple=\"1\"" : "";
 $m4is_b18924 =isset($m4is_q523['change'])? $m4is_q523['change']: false;
 $m4is_h086 =!empty($m4is_b18924)? " data-change=\"{$m4is_b18924
}\"" : "";
 $m4is_h086 .= isset($m4is_q523['disable-search'])? " data-disable-search=\"1\"" : "";
 echo sprintf('<input type="text" name="%s" id="%s" value="%s" class="widefat %s-field-input" data-memb-asset-select2="%s"%s%s/>', $m4is_s36520, $m4is_m661, esc_attr($m4is_v586), $m4is_j67631, $m4is_l91805, $m4is_c6792, $m4is_h086 );
 
}elseif($m4is_j0361 === 'textarea'){
$m4is_m615 =isset($m4is_q523['rows'])? (int)$m4is_q523['rows']: 1;
 echo sprintf('<textarea name="%s" id="%s" class="widefat %s-field-textarea" rows="%s">%s</textarea>', $m4is_s36520, $m4is_m661, $m4is_j67631, $m4is_m615, $m4is_v586 );
 
}if(isset($m4is_q523['desc'])&&$m4is_q523['desc']> '' ){
echo sprintf('<p class="memb-asset-access-description description">%s</p>', $m4is_q523['desc']);
 
}echo '</div>';
 
}?>

</fieldset>

