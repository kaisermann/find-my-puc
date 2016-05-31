<?php 
Neo::executeQuery('match (n) return n', 0, 1);
$layout = NULL;
if(!$GLOBALS['neo4jOn'])
	$layout = 'error';
else
{
	if(!isset($Request[PAGE_ID]) || strlen($Request[PAGE_ID])==0)
		$layout = 'home';
	else
		switch ($Request[PAGE_ID]) 
	{
		case 'contato':
		$layout = 'contato';
		break;

		default: 
		$layout = '404';
		break;
	}
}
?>
<?php getTemplatePart("layout/components/head"); ?>

<body class="js--loading <?php body_class(); ?>">
	<div class="page-wrapper">
		<?php 
		if($GLOBALS['neo4jOn'])
			getTemplatePart("layout/components/header"); 
		?>
		<main class="main <?php if(!$GLOBALS['neo4jOn']) echo 'main--full'; ?>" data-page="<?php echo $layout; ?>">
			<div class="main__content">
				<?php getTemplatePart('layout/pages/'.$layout.''); ?>
				
				<?php //getTemplatePart('layout/components/footer'); ?>
			</div>
		</main>
	</div>
	<script type="text/javascript">
		var base_url = '<?php echo ROOT_DIR; ?>';
		var ajax_url = '<?php echo ROOT_DIR.'ajax.php'; ?>';
	</script>
	<?php Main::assetQueue('footer'); ?>
	<div class="spinner-wrapper">
		<div class="spinner"></div>
	</div>
</body>
</html>