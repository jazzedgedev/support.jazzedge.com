<div class='rg-container hover-black'>
    <p>Scroll through the items within the box below:</p>
	<table class='rg-table zebra' summary='Hed'>
		<thead>
			<tr>
				<th class='text'>ID</th>
				<th class='text'>Focus</th>
			</tr>
		</thead>
		<tbody>
<?php
  global $wpdb;
  $query = $wpdb->prepare("SELECT * FROM je_practice_curriculum ORDER BY ID ASC");
  $items = $wpdb->get_results($query);
  foreach ($items AS $item){
    echo "<tr><td>$item->ID</td><td>$item->focus_title</td></tr>";
    
  }
?>
		</tbody>
	</table>
</div>