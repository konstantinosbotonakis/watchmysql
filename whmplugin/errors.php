<?PHP
if($watchmysql->get_errors() !== false) {
	foreach($watchmysql->get_errors() as $my_error) {
?>
		<div class="ndc_error"><?=$my_error;?></div>
<?PHP
	}
}
?>
